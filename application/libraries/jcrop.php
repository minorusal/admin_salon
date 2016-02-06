<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class jcrop {
    private $src;
    private $tmp = 'assets/tmp/';
    private $data;
    private $file;
    private $dst;
    private $type;
    private $extension;
    private $msg;
    private $ci;

    function __construct() {
      $this->ci =& get_instance();
    }

    public function initialize_crop($src_origin, $src_dst,  $data, $file){
        $this->setSrc($src_origin,$src_dst);
        $this->setData($data);
        $this->setFile($file,$src_dst);

        $this->crop($this->src, $this->dst, $this->data);
        $response = array(
                  'state'   => 200,
                  'message' => $this->getMsg(),
                  'result'  => $this->getResult()
                );
        return $response;
    }
   
    private function setSrc($src,$src_dst) {
        if (!empty($src)) {
            $type = exif_imagetype($src);

            if ($type) {
                  $this->src       = $src;
                  $this->type      = $type;
                  $this->extension = image_type_to_extension($type);
                  $this->setDst($src_dst);
            }
        }
    }

    private function setData($data) {
        if (!empty($data)) {
            $this->data = json_decode(stripslashes($data));
        }
    }

    private function setFile($file,$src_dst) {
        $errorCode = $file['error'];
        if ($errorCode === UPLOAD_ERR_OK) {
            $type = exif_imagetype($file['tmp_name']);
        if ($type) {
            $extension = image_type_to_extension($type);
            $src = $this->tmp. date('YmdHis') . '.original' . $extension;

            if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_JPEG || $type == IMAGETYPE_PNG) {

                if (file_exists($src)) {
                    unlink($src);
                }

                $result = move_uploaded_file($file['tmp_name'], $src);

                if ($result) {
                    $this->src       = $src;
                    $this->type      = $type;
                    $this->extension = $extension;
                    $this->setDst($src_dst);
                }else{
                    $this->msg = 'Failed to save file';
                }
            }else{
                $this->msg = 'Please upload image with the following types: JPG, PNG, GIF';
            }
        }else{
            $this->msg = 'Please upload image file';
        }
      }else{
           $this->msg = $this -> codeToMessage($errorCode);
      }
    }

    private function setDst($src_dst) {
        if(isset($src_dst)){
            $this->dst =  $src_dst . '.png';
        }else{
            $this->dst =  $this->tmp.date('YmdHis') . '.png';    
        }
        
    }

    private function crop($src, $dst, $data) {
        if (!empty($src) && !empty($dst) && !empty($data)) {
            switch ($this->type) {
                case IMAGETYPE_GIF:
                    $src_img = imagecreatefromgif($src);
                    break;

                case IMAGETYPE_JPEG:
                    $src_img = imagecreatefromjpeg($src);
                    break;

                case IMAGETYPE_PNG:
                    $src_img = imagecreatefrompng($src);
                    break;
        }

        if (!$src_img) {
            $this->msg = "Failed to read the image file";
            return;
        }

        $size   = getimagesize($src);
        $size_w = $size[0]; // natural width
        $size_h = $size[1]; // natural height

        $src_img_w = $size_w;
        $src_img_h = $size_h;

        $degrees = $data->rotate;

        // Rotate the source image
        if (is_numeric($degrees) && $degrees != 0) {
          // PHP's degrees is opposite to CSS's degrees
            $new_img = imagerotate( $src_img, -$degrees, imagecolorallocatealpha($src_img, 0, 0, 0, 127) );

            imagedestroy($src_img);
            $src_img = $new_img;

            $deg = abs($degrees) % 180;
            $arc = ($deg > 90 ? (180 - $deg) : $deg) * M_PI / 180;

            $src_img_w = $size_w * cos($arc) + $size_h * sin($arc);
            $src_img_h = $size_w * sin($arc) + $size_h * cos($arc);

            // Fix rotated image miss 1px issue when degrees < 0
            $src_img_w -= 1;
            $src_img_h -= 1;
        }

        $tmp_img_w = $data->width;
        $tmp_img_h = $data->height;
        $dst_img_w = 220;
        $dst_img_h = 220;

        $src_x = $data -> x;
        $src_y = $data -> y;

        if ($src_x <= -$tmp_img_w || $src_x > $src_img_w) {
            $src_x = $src_w = $dst_x = $dst_w = 0;
        } else if ($src_x <= 0) {
            $dst_x = -$src_x;
            $src_x = 0;
            $src_w = $dst_w = min($src_img_w, $tmp_img_w + $src_x);
        } else if ($src_x <= $src_img_w) {
            $dst_x = 0;
            $src_w = $dst_w = min($tmp_img_w, $src_img_w - $src_x);
        }

        if ($src_w <= 0 || $src_y <= -$tmp_img_h || $src_y > $src_img_h) {
            $src_y = $src_h = $dst_y = $dst_h = 0;
        } else if ($src_y <= 0) {
            $dst_y = -$src_y;
            $src_y = 0;
            $src_h = $dst_h = min($src_img_h, $tmp_img_h + $src_y);
        } else if ($src_y <= $src_img_h) {
            $dst_y = 0;
            $src_h = $dst_h = min($tmp_img_h, $src_img_h - $src_y);
        }

        // Scale to destination position and size
        $ratio = $tmp_img_w / $dst_img_w;
        $dst_x /= $ratio;
        $dst_y /= $ratio;
        $dst_w /= $ratio;
        $dst_h /= $ratio;

        $dst_img = imagecreatetruecolor($dst_img_w, $dst_img_h);

        // Add transparent background to destination image
        imagefill($dst_img, 0, 0, imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
        imagesavealpha($dst_img, true);

        $result = imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, 220,220, $src_w, $src_h);

        if ($result) {
            //die($dst_img);
            if (!imagepng($dst_img, $dst)) {
                $this->msg = "Failed to save the cropped image file";
            }
        } else {
            $this->msg = "Failed to crop the image file";
        }

        imagedestroy($src_img);
        imagedestroy($dst_img);
        unlink($src);
      }
    }

    private function codeToMessage($code) {
          switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                break;

            case UPLOAD_ERR_FORM_SIZE:
                $message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                break;

            case UPLOAD_ERR_PARTIAL:
                $message = 'The uploaded file was only partially uploaded';
                break;

            case UPLOAD_ERR_NO_FILE:
                $message = 'No file was uploaded';
                break;

            case UPLOAD_ERR_NO_TMP_DIR:
                $message = 'Missing a temporary folder';
                break;

            case UPLOAD_ERR_CANT_WRITE:
                $message = 'Failed to write file to disk';
                break;

            case UPLOAD_ERR_EXTENSION:
                $message = 'File upload stopped by extension';
                break;

            default:
                $message = 'Unknown upload error';
          }

          return $message;
    }

    public function getResult() {
        return !empty($this->data) ? base_url().$this->dst : $this->src;
    }

    public function getMsg() {
        return $this->msg;
    }
}
?>
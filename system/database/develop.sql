-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 06-03-2015 a las 12:58:37
-- Versión del servidor: 5.6.17
-- Versión de PHP: 5.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `develop`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_claves`
--

CREATE TABLE IF NOT EXISTS `sys_claves` (
`id_clave` int(11) NOT NULL,
  `user` varchar(20) DEFAULT NULL,
  `pwd` varchar(50) DEFAULT NULL,
  `registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `sys_claves`
--

INSERT INTO `sys_claves` (`id_clave`, `user`, `pwd`, `registro`, `activo`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', '2015-02-03 15:02:22', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_empresas`
--

CREATE TABLE IF NOT EXISTS `sys_empresas` (
`id_empresa` int(11) NOT NULL,
  `empresa` varchar(50) DEFAULT NULL,
  `razon_social` varchar(50) DEFAULT NULL,
  `rfc` varchar(50) DEFAULT NULL,
  `direccion` text,
  `telefono` varchar(50) DEFAULT NULL,
  `image` varchar(50) DEFAULT NULL,
  `registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `sys_empresas`
--

INSERT INTO `sys_empresas` (`id_empresa`, `empresa`, `razon_social`, `rfc`, `direccion`, `telefono`, `image`, `registro`, `activo`) VALUES
(1, 'contempo', 'gcontempo', 'gc03022015', 'insurgentes sur', '5555555', NULL, '2015-02-03 17:15:57', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_modulos`
--

CREATE TABLE IF NOT EXISTS `sys_modulos` (
`id_modulo` int(11) NOT NULL,
  `modulo` varchar(70) DEFAULT NULL,
  `routes` varchar(150) NOT NULL DEFAULT '404_override',
  `registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `sys_modulos`
--

INSERT INTO `sys_modulos` (`id_modulo`, `modulo`, `routes`, `registro`, `activo`) VALUES
(1, 'inicio', '404_override', '2015-02-09 13:11:03', 1),
(2, 'inventario', '404_override', '2015-02-09 13:11:03', 1),
(3, 'reportes', '404_override', '2015-02-09 13:11:03', 1),
(4, 'configuraciones', '404_override', '2015-02-09 13:11:03', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_paises`
--

CREATE TABLE IF NOT EXISTS `sys_paises` (
`id_pais` int(11) NOT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `dominio` varchar(11) DEFAULT NULL,
  `avatar` varchar(200) DEFAULT NULL,
  `registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `sys_paises`
--

INSERT INTO `sys_paises` (`id_pais`, `pais`, `dominio`, `avatar`, `registro`, `activo`) VALUES
(1, 'Mexico', 'mx', 'country/mx.png', '2015-02-10 15:50:40', 1),
(2, 'Costa Rica', 'cr', 'country/cr.png', '2015-02-10 15:50:40', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_perfiles`
--

CREATE TABLE IF NOT EXISTS `sys_perfiles` (
`id_perfil` int(11) NOT NULL,
  `perfil` varchar(50) DEFAULT NULL,
  `id_modulo` varchar(200) NOT NULL DEFAULT '1',
  `registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `sys_perfiles`
--

INSERT INTO `sys_perfiles` (`id_perfil`, `perfil`, `id_modulo`, `registro`, `activo`) VALUES
(1, 'ROOT', '1,2,3,4', '2015-02-09 13:36:58', 1),
(2, 'ADMINISTRADOR', '1,2,3,4', '2015-02-09 13:36:58', 1),
(3, 'GERENTE', '1,2', '2015-02-09 13:36:58', 1),
(4, 'CAJERO', '1', '2015-02-09 13:36:58', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_personales`
--

CREATE TABLE IF NOT EXISTS `sys_personales` (
`id_personal` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `paterno` varchar(50) DEFAULT NULL,
  `materno` varchar(50) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `mail` varchar(50) DEFAULT NULL,
  `avatar` varchar(50) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `sys_personales`
--

INSERT INTO `sys_personales` (`id_personal`, `nombre`, `paterno`, `materno`, `telefono`, `mail`, `avatar`) VALUES
(1, 'jorge', 'martinez', 'carreto', '555555555', 'jorge.martinez@isolution.mx', 'users/00001.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_submodulos`
--

CREATE TABLE IF NOT EXISTS `sys_submodulos` (
`id_submodulo` int(11) NOT NULL,
  `submodulo` varchar(100) NOT NULL,
  `routes` varchar(150) NOT NULL DEFAULT '404_override',
  `id_modulo` int(11) NOT NULL,
  `registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `sys_submodulos`
--

INSERT INTO `sys_submodulos` (`id_submodulo`, `submodulo`, `routes`, `id_modulo`, `registro`, `activo`) VALUES
(1, 'usuarios', '404_override', 4, '2015-02-26 09:23:18', 1),
(2, 'empresa', '404_override', 4, '2015-03-04 09:41:49', 1),
(3, 'alta', '404_override', 2, '2015-03-04 16:29:47', 1),
(4, 'bajas', 'bajas', 2, '2015-03-04 17:39:08', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_sucursales`
--

CREATE TABLE IF NOT EXISTS `sys_sucursales` (
`id_sucursal` int(11) NOT NULL,
  `sucursal` varchar(60) DEFAULT NULL,
  `razon_social` varchar(60) DEFAULT NULL,
  `rfc` varchar(30) DEFAULT NULL,
  `direccion` text,
  `telefono` varchar(50) DEFAULT NULL,
  `image` varchar(50) NOT NULL,
  `registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `sys_sucursales`
--

INSERT INTO `sys_sucursales` (`id_sucursal`, `sucursal`, `razon_social`, `rfc`, `direccion`, `telefono`, `image`, `registro`, `activo`) VALUES
(1, 'sucursal prueba', 'sprueba', 'sp3862', 'insurgentes sur', '555555555', '', '2015-02-03 17:13:20', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_usuarios`
--

CREATE TABLE IF NOT EXISTS `sys_usuarios` (
`id_usuario` int(11) NOT NULL,
  `id_personal` int(11) NOT NULL,
  `id_clave` int(11) NOT NULL,
  `id_perfil` int(11) DEFAULT NULL,
  `id_pais` int(11) DEFAULT NULL,
  `id_empresa` int(11) DEFAULT NULL,
  `id_sucursal` int(11) DEFAULT NULL,
  `registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `activo` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `sys_usuarios`
--

INSERT INTO `sys_usuarios` (`id_usuario`, `id_personal`, `id_clave`, `id_perfil`, `id_pais`, `id_empresa`, `id_sucursal`, `registro`, `activo`) VALUES
(1, 1, 1, 2, 1, 1, 1, '2015-02-04 16:14:33', 1),
(3, 1, 1, 3, 1, 1, 1, '2015-02-05 15:27:07', 1),
(4, 1, 1, 4, 2, 1, 1, '2015-02-09 10:06:00', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `sys_claves`
--
ALTER TABLE `sys_claves`
 ADD PRIMARY KEY (`id_clave`);

--
-- Indices de la tabla `sys_empresas`
--
ALTER TABLE `sys_empresas`
 ADD PRIMARY KEY (`id_empresa`);

--
-- Indices de la tabla `sys_modulos`
--
ALTER TABLE `sys_modulos`
 ADD PRIMARY KEY (`id_modulo`);

--
-- Indices de la tabla `sys_paises`
--
ALTER TABLE `sys_paises`
 ADD PRIMARY KEY (`id_pais`);

--
-- Indices de la tabla `sys_perfiles`
--
ALTER TABLE `sys_perfiles`
 ADD PRIMARY KEY (`id_perfil`), ADD KEY `id_modulo` (`id_modulo`);

--
-- Indices de la tabla `sys_personales`
--
ALTER TABLE `sys_personales`
 ADD PRIMARY KEY (`id_personal`);

--
-- Indices de la tabla `sys_submodulos`
--
ALTER TABLE `sys_submodulos`
 ADD PRIMARY KEY (`id_submodulo`), ADD KEY `id_modulo` (`id_modulo`), ADD KEY `id_modulo_2` (`id_modulo`);

--
-- Indices de la tabla `sys_sucursales`
--
ALTER TABLE `sys_sucursales`
 ADD PRIMARY KEY (`id_sucursal`);

--
-- Indices de la tabla `sys_usuarios`
--
ALTER TABLE `sys_usuarios`
 ADD PRIMARY KEY (`id_usuario`), ADD UNIQUE KEY `id_nivel_2` (`id_perfil`), ADD KEY `id_personal` (`id_personal`), ADD KEY `id_clave` (`id_clave`), ADD KEY `id_nivel` (`id_perfil`), ADD KEY `id_empresa` (`id_empresa`), ADD KEY `id_sucursal` (`id_sucursal`), ADD KEY `id_sucursal_2` (`id_sucursal`), ADD KEY `id_empresa_2` (`id_empresa`), ADD KEY `id_personal_2` (`id_personal`), ADD KEY `id_perfil` (`id_perfil`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `sys_claves`
--
ALTER TABLE `sys_claves`
MODIFY `id_clave` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `sys_empresas`
--
ALTER TABLE `sys_empresas`
MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `sys_modulos`
--
ALTER TABLE `sys_modulos`
MODIFY `id_modulo` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT de la tabla `sys_paises`
--
ALTER TABLE `sys_paises`
MODIFY `id_pais` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT de la tabla `sys_perfiles`
--
ALTER TABLE `sys_perfiles`
MODIFY `id_perfil` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT de la tabla `sys_personales`
--
ALTER TABLE `sys_personales`
MODIFY `id_personal` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `sys_submodulos`
--
ALTER TABLE `sys_submodulos`
MODIFY `id_submodulo` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT de la tabla `sys_sucursales`
--
ALTER TABLE `sys_sucursales`
MODIFY `id_sucursal` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `sys_usuarios`
--
ALTER TABLE `sys_usuarios`
MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

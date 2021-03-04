/*
 Navicat Premium Data Transfer

 Source Server         : 本地数据库
 Source Server Type    : MySQL
 Source Server Version : 80012
 Source Host           : 127.0.0.1:3306
 Source Schema         : cms

 Target Server Type    : MySQL
 Target Server Version : 80012
 File Encoding         : 65001

 Date: 30/12/2020 11:06:55
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for cms_admin_menu
-- ----------------------------

-- ----------------------------
-- Records of cms_admin_menu
-- ----------------------------

INSERT INTO `cms_admin_menu` VALUES (13, 2, '操作日志', '', 1, '/auth/operationLog', NULL, NULL, 1, 1000);

-- ----------------------------
-- Table structure for cms_operation_module
-- ----------------------------
DROP TABLE IF EXISTS `cms_operation_module`;
CREATE TABLE `cms_operation_module`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '操作模块表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cms_operation_module
-- ----------------------------
INSERT INTO `cms_operation_module` VALUES (1, '其他');
INSERT INTO `cms_operation_module` VALUES (2, '登录');
INSERT INTO `cms_operation_module` VALUES (3, '权限管理');

-- ----------------------------
-- Table structure for cms_operation_log
-- ----------------------------
DROP TABLE IF EXISTS `cms_operation_log`;
CREATE TABLE `cms_operation_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '操作人ID',
  `module_id` int(11) NOT NULL DEFAULT 0 COMMENT '操作模块ID',
  `content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作内容',
  `ip` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作IP（支持IPV6存储）',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `raw_data` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '原始数据',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `module_id`(`module_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '操作日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cms_operation_log
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;

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
-- Table structure for cms_admin
-- ----------------------------
DROP TABLE IF EXISTS `cms_admin`;
CREATE TABLE `cms_admin`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '姓名',
  `mobile` char(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '手机号',
  `account` char(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '账号',
  `password` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码',
  `status` tinyint(1) UNSIGNED NULL DEFAULT 1 COMMENT '状态 1：正常 0：禁用',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `delete_time` datetime NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`, `account`) USING BTREE,
  INDEX `status`(`status`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '管理员表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cms_admin
-- ----------------------------
INSERT INTO `cms_admin` VALUES (1, '', '15162862298', 'admin', 'f917e99595ebe192a689535ae8ff6779', 1, '', '2020-12-24 15:13:47', '2020-12-24 15:13:47', NULL);

-- ----------------------------
-- Table structure for cms_admin_menu
-- ----------------------------
DROP TABLE IF EXISTS `cms_admin_menu`;
CREATE TABLE `cms_admin_menu`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父ID',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '规则名称',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '规则备注',
  `type` tinyint(1) UNSIGNED NOT NULL COMMENT '类型 1：菜单 2：隐藏',
  `rule` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '规则',
  `param` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '额外参数',
  `icon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '图标class或name',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态 1：正常 0：禁用',
  `order` int(11) UNSIGNED NOT NULL DEFAULT 1000 COMMENT '排序 大的在后',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `parent_id`(`parent_id`) USING BTREE,
  INDEX `status`(`status`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '后台菜单（规则）表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cms_admin_menu
-- ----------------------------
INSERT INTO `cms_admin_menu` VALUES (1, 0, '首页', '', 1, '/', NULL, 'odometer', 1, 100);

INSERT INTO `cms_admin_menu` VALUES (2, 0, '权限管理', '', 1, '/auth', NULL, 'set-up', 1, 1000);

INSERT INTO `cms_admin_menu` VALUES (3, 2, '账号管理', '', 1, '/auth/account', NULL, NULL, 1, 1000);
INSERT INTO `cms_admin_menu` VALUES (4, 3, '查看', '', 2, '/auth/account/data', NULL, NULL, 1, 1000);
INSERT INTO `cms_admin_menu` VALUES (5, 3, '新增/编辑账号', '', 2, '/auth/account/edit', NULL, NULL, 1, 1000);
INSERT INTO `cms_admin_menu` VALUES (6, 3, '删除账号', '', 2, '/auth/account/del', NULL, NULL, 1, 1000);
INSERT INTO `cms_admin_menu` VALUES (7, 3, '重置密码', '', 2, '/auth/account/rePwd', NULL, NULL, 1, 1000);
INSERT INTO `cms_admin_menu` VALUES (8, 3, '设定状态', '', 2, '/auth/account/setStatus', NULL, NULL, 1, 1000);

INSERT INTO `cms_admin_menu` VALUES (9, 2, '角色管理', '', 1, '/auth/role', NULL, NULL, 1, 1000);
INSERT INTO `cms_admin_menu` VALUES (10, 9, '查看', '', 2, '/auth/role/data', NULL, NULL, 1, 1000);
INSERT INTO `cms_admin_menu` VALUES (11, 9, '新增/编辑角色', '', 2, '/auth/role/edit', NULL, NULL, 1, 1000);
INSERT INTO `cms_admin_menu` VALUES (12, 9, '删除角色', '', 2, '/auth/role/del', NULL, NULL, 1, 1000);

-- ----------------------------
-- Table structure for cms_role
-- ----------------------------
DROP TABLE IF EXISTS `cms_role`;
CREATE TABLE `cms_role`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态 1：正常 0：禁用',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `delete_time` datetime NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `status`(`status`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cms_role
-- ----------------------------
INSERT INTO `cms_role` VALUES (1, '超级管理员', 1, '', '2020-12-28 16:44:53', '2020-12-30 10:55:18', NULL);

-- ----------------------------
-- Table structure for cms_role_admin
-- ----------------------------
DROP TABLE IF EXISTS `cms_role_admin`;
CREATE TABLE `cms_role_admin`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `role_id`(`role_id`) USING BTREE,
  INDEX `admin_id`(`admin_id`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '管理员角色关联表' ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of cms_role_admin
-- ----------------------------
INSERT INTO `cms_role_admin` VALUES (1, 1, 1);

-- ----------------------------
-- Table structure for cms_role_menu
-- ----------------------------
DROP TABLE IF EXISTS `cms_role_menu`;
CREATE TABLE `cms_role_menu`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NULL DEFAULT NULL,
  `menu_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `role_id`(`role_id`) USING BTREE,
  INDEX `menu_id`(`menu_id`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '菜单角色关联表' ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of cms_role_menu
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;

/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `eb_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_admin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户账号',
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户密码',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户头像',
  `name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户姓名',
  `phone` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `job` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '职位ID',
  `is_admin` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为超级管理员',
  `roles` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '角色权限',
  `uni_online` tinyint(1) NOT NULL DEFAULT '0' COMMENT '移动端登录状态',
  `client_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '连接通道ID',
  `scan_key` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '扫码登录参数',
  `last_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '访问ip',
  `login_count` int(11) NOT NULL DEFAULT '0' COMMENT '登陆次数',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：0、锁定；1、正常；',
  `is_init` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否为初始密码',
  `language` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'zh-cn' COMMENT '语言',
  `mark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uid` (`uid`) USING BTREE,
  KEY `eb_admin_account_index` (`account`) USING BTREE,
  KEY `eb_admin_name_index` (`name`) USING BTREE,
  KEY `eb_admin_phone_index` (`phone`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业员工表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_admin_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_admin_info` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `letter` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '#' COMMENT '姓氏首字母',
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `area` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `card_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证号',
  `province` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `birthday` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '员工生日',
  `nation` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '员工种族',
  `politic` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '政治面貌',
  `education` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学历',
  `education_image` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学历证书',
  `acad` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学位',
  `acad_image` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学位证书',
  `native` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '籍贯',
  `address` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '居住地',
  `sex` tinyint(4) NOT NULL DEFAULT '0' COMMENT '性别: 0、未知；1、男；2、女；3、其他；',
  `age` tinyint(3) unsigned DEFAULT NULL COMMENT '员工年龄',
  `marriage` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '婚姻状况:0、未婚；1、已婚；',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '员工状态:0、未入职；1、正式;2、使用;3、实习;4、离职；',
  `work_years` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '工作经验（年）',
  `spare_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '紧急联系人',
  `spare_tel` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '紧急联系电话',
  `email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `social_num` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '社保账户',
  `fund_num` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '公积金账户',
  `bank_num` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '银行卡账户',
  `bank_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '开户行',
  `graduate_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '毕业院校',
  `graduate_date` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '毕业时间',
  `interview_date` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '面试时间',
  `interview_position` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '面试职位',
  `is_part` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否兼职',
  `photo` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '员工照片',
  `card_front` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证正面',
  `card_both` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证背面',
  `work_time` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '入职时间',
  `trial_time` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '试用时间',
  `formal_time` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '转正时间',
  `treaty_time` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '合同到期时间',
  `quit_time` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '离职时间',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业员工档案表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_agreement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_agreement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ident` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '协议标识',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '协议标题',
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '协议内容',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户协议表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_approve`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_approve` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `card_id` bigint(20) unsigned NOT NULL,
  `entid` bigint(20) unsigned NOT NULL,
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '审批名称',
  `icon` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '审批图标',
  `color` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '审批图标颜色',
  `info` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '审批说明',
  `types` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审批类型：见枚举；',
  `examine` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '是否需要审核',
  `config` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表单配置详情',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态：0、关闭；1、开启；',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_approve_card_id_foreign` (`card_id`) USING BTREE,
  KEY `eb_enterprise_approve_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='审批配置表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_approve_apply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_approve_apply` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `card_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户名片ID',
  `approve_id` bigint(20) unsigned NOT NULL,
  `node_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '当前节点ID',
  `examine` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '是否需要审批：0、无须审批；1、需要审批；',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '申请状态：-1、撤回；0、待审批；1、已通过；2、已拒绝；',
  `info` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '说明',
  `number` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '编号',
  `crud_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联实体ID',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '实体数据ID',
  `apply_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联审批ID',
  `is_recall` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否为撤销审批',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_approve_apply_approve_id_foreign` (`approve_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='审批申请表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_approve_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_approve_content` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `card_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户名片ID',
  `approve_id` bigint(20) unsigned NOT NULL,
  `apply_id` bigint(20) unsigned NOT NULL,
  `title` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表单名称',
  `info` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表单提示',
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表单默认值',
  `required` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否必选',
  `types` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表单类型',
  `symbol` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字段标识',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表单详情',
  `props` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '限制条件',
  `options` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表单配置信息',
  `config` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表单配置信息',
  `uniqued` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表单唯一值',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_approve_content_approve_id_foreign` (`approve_id`) USING BTREE,
  KEY `eb_enterprise_approve_content_apply_id_foreign` (`apply_id`) USING BTREE,
  KEY `eb_approve_content_symbol_index` (`symbol`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='审批申请内容表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_approve_form`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_approve_form` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `card_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户名片ID',
  `approve_id` bigint(20) unsigned NOT NULL,
  `title` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表单名称',
  `info` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表单提示',
  `value` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表单默认值',
  `required` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否必选',
  `types` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表单类型',
  `symbol` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字段标识',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表单详情',
  `props` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '限制条件',
  `options` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表单配置信息',
  `config` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表单配置信息',
  `uniqued` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表单唯一值',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_approve_form_approve_id_foreign` (`approve_id`) USING BTREE,
  KEY `eb_approve_form_symbol_index` (`symbol`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='审批配置表单表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_approve_holiday_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_approve_holiday_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '假期类型',
  `new_employee_limit` tinyint(4) NOT NULL DEFAULT '0' COMMENT '新员工请假限制：0、不限制；1、限制；',
  `new_employee_limit_month` tinyint(4) NOT NULL DEFAULT '1' COMMENT '新员工请假月时限制',
  `duration_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '请假时长类型：0、天；1、小时；',
  `duration_calc_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '时长计算类型：0、自然日；1、工作日；',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='审批假期表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_approve_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_approve_process` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建企业ID',
  `card_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户名片ID',
  `approve_id` bigint(20) unsigned NOT NULL,
  `level` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '流程级别',
  `groups` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '分组ID',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '节点名称',
  `types` tinyint(3) unsigned NOT NULL COMMENT '节点类型：0、申请人；1、审批人；2、抄送人；3、条件；4、路由；',
  `uniqued` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '节点唯一值',
  `settype` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '审核人类型：1、指定成员；2、指定部门主管；7、连续多部门；5、申请人自己；4、申请人自选；(0、无此条件)',
  `director_order` tinyint(4) NOT NULL DEFAULT '-1' COMMENT '指定层级顺序：0、从上至下；1、从下至上；(-1、无此条件)',
  `director_level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '指定主管层级/指定终点层级：1-10；(0、无此条件)',
  `no_hander` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '当前部门无负责人时：1、上级部门负责人审批；2、为空时跳过；(0、无此条件)',
  `dep_head` text COLLATE utf8mb4_unicode_ci COMMENT '指定部门负责人',
  `self_select` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许自选抄送人',
  `select_range` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '可选范围：1、不限范围；2、指定成员；(0、无此条件)',
  `user_list` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '指定的成员列表',
  `select_mode` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '选人方式：1、单选；2、多选；(0、无此条件)',
  `examine_mode` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '多人审批方式：1、或签；2、会签；3、依次审批；(0、无此条件)',
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '条件优先级',
  `parent` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '节点父级唯一值',
  `is_child` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否存在子节点',
  `is_condition` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否存在条件',
  `condition_list` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件详情',
  `is_initial` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为初始数据',
  `info` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据详情',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_approve_process_approve_id_foreign` (`approve_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='审批配置流程表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_approve_reply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_approve_reply` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `card_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户名片ID',
  `apply_id` bigint(20) unsigned NOT NULL,
  `content` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '回复内容',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_approve_reply_apply_id_foreign` (`apply_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='审批申请评价表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_approve_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_approve_rule` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `card_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户名片ID',
  `approve_id` bigint(20) unsigned NOT NULL,
  `range` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '可见范围',
  `abnormal` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '异常处理：0、自动同意；指定处理人ID；',
  `auto` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '自动审批：0、首个节点处理，其他自动同意；1、连续审批自动同意；2、每个节点都需审批；',
  `edit` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '修改权限：0、员工不可修改固定人员；1、不可删除固定抄送人；',
  `recall` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '异常处理：1、审批通过后允许撤销；',
  `is_transfer` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转审',
  `is_sign` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否可加签',
  `refuse` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '被拒绝后：0、返回初始，所有人重新审批；1、跳过已通过层级；',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_approve_rule_approve_id_foreign` (`approve_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='审批配置规则表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_approve_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_approve_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `card_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '相关用户名片ID',
  `approve_id` bigint(20) unsigned NOT NULL,
  `apply_id` bigint(20) unsigned NOT NULL,
  `node_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '审核节点ID(唯一值)',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '级别',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '审批顺序',
  `verify` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '操作状态：0、自动；1、手动；',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '审批状态：-1、无需审批；0、待审批；1、已通过；2、已拒绝；',
  `is_sign` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否为加签',
  `is_transfer` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否为转审：0、正常节点；1、已转审；2、被转审；',
  `parent` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '转审人ID',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '类型：1、审核人；2、抄送人；',
  `info` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '人员详情',
  `process_info` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '流程节点详情',
  `content` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '人员说明',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_approve_user_approve_id_foreign` (`approve_id`) USING BTREE,
  KEY `eb_enterprise_approve_user_apply_id_foreign` (`apply_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='审批用户表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_assess`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_assess` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `period` tinyint(4) NOT NULL DEFAULT '0' COMMENT '周期:1=周;2=月;3=年',
  `planid` bigint(20) unsigned NOT NULL,
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '组织架构ID',
  `number` int(11) NOT NULL DEFAULT '0' COMMENT '考核批次ID',
  `check_uid` int(11) NOT NULL DEFAULT '0' COMMENT '考核用户信息表ID',
  `test_uid` int(11) NOT NULL DEFAULT '0' COMMENT '被考核用户信息表ID',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '考核开始时间',
  `make_time` timestamp NULL DEFAULT NULL COMMENT '目标制定时间结束时间',
  `make_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '目标制定状态：0、未制定；1、已启用；2、草稿。',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '考核结束时间',
  `test_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '自评状态：0、未评价；1、已评价；2、草稿；',
  `check_end` timestamp NULL DEFAULT NULL COMMENT '上级评价结束时间',
  `check_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上级评价状态：0、未评价；1、已评价；2、草稿。',
  `verify_time` timestamp NULL DEFAULT NULL COMMENT '审核结束时间',
  `verify_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '审核状态：0、未审核；1、已审核；',
  `score` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '考核得分',
  `total` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '最高分',
  `grade` int(11) NOT NULL DEFAULT '0' COMMENT '考核等级',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '考核状态：0、目标制定；1、自评期；2、上级评价；3、审核期；4、结束；',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '评分方式：0、加权评分；1、加和评分',
  `intact` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '完整性：1、是；0、否',
  `is_show` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用：0、未启用；1、已启用；',
  `self_reply` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '自评',
  `reply` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上级评价',
  `hide_reply` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上级评价(仅上级可见)',
  `delete` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_assess_self_reply_index` (`self_reply`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='绩效考核表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_assess_frame`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_assess_frame` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` tinyint(4) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `planid` bigint(20) unsigned NOT NULL,
  `test_frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '企业组织架构表',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_assess_frame_planid_foreign` (`planid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_assess_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_assess_plan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL,
  `create_time` tinyint(1) NOT NULL DEFAULT '1' COMMENT '星期:1-7/或者几号1-31',
  `create_month` int(11) NOT NULL DEFAULT '0' COMMENT '月',
  `assess_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '被考核人类型：0=人员添加,1=部门添加',
  `period` tinyint(4) NOT NULL DEFAULT '0' COMMENT '周期:1=周;2=月;3=年;5=季度;4=半年',
  `make_type` enum('before','after','start') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'before' COMMENT '目标制定时间类型：考核开始前、考核开始后',
  `make_day` int(11) NOT NULL DEFAULT '0' COMMENT '目标制定天数',
  `eval_type` enum('before','after','start') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'before' COMMENT '上级评价时间类型：考核结束前、考核结束后',
  `eval_day` int(11) NOT NULL DEFAULT '0' COMMENT '上级评价天数',
  `verify_type` enum('before','after') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'after' COMMENT '审核时间类型：评价结束前、评价结束后',
  `verify_day` int(11) NOT NULL DEFAULT '0' COMMENT '绩效审核天数',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:0=禁用;1=开启',
  `uniqued` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务唯一值',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_assess_plan_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='绩效考核计划';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_assess_plan_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_assess_plan_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `planid` bigint(20) unsigned NOT NULL,
  `test_uid` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='绩效考核计划人员表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_assess_reply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_assess_reply` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `assessid` bigint(20) unsigned NOT NULL,
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '企业用户ID',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `is_own` tinyint(4) NOT NULL DEFAULT '0' COMMENT '自身可见：0、否；1、是',
  `types` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型：0、评价；1、申诉',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '申诉状态：0、评价；1、已处理；2、已拒绝；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='绩效考核评价表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_assess_scheme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_assess_scheme` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `period` tinyint(4) NOT NULL DEFAULT '0' COMMENT '周期:1=周;2=月;3=年',
  `create_type` enum('time','monday','tuesday','wednesday','thursday','friday','saturday','sunday') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'time' COMMENT '生成绩效日期类型',
  `create_month` int(11) NOT NULL DEFAULT '0' COMMENT '生成绩效月份',
  `create_day` int(11) NOT NULL DEFAULT '0' COMMENT '生成绩效日期',
  `create_time` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '生成绩效时间',
  `own_appraise_period` enum('year','nextyear','month','nextmonth','monday','tuesday','wednesday','thursday','friday','saturday','sunday') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'year' COMMENT '自评结束时间类型',
  `own_appraise_month` int(11) NOT NULL DEFAULT '0' COMMENT '自评结束月份',
  `own_appraise_day` int(11) NOT NULL DEFAULT '0' COMMENT '自评结束日期',
  `own_appraise_time` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '自评结束时间',
  `leader_appraise_period` enum('year','nextyear','month','nextmonth','monday','tuesday','wednesday','thursday','friday','saturday','sunday') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'year' COMMENT '上级评分结束时间类型',
  `leader_appraise_month` int(11) NOT NULL DEFAULT '0' COMMENT '上级评分结束月份',
  `leader_appraise_day` int(11) NOT NULL DEFAULT '0' COMMENT '上级评分结束日期',
  `leader_appraise_time` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上级评分结束时间',
  `user_id` int(11) NOT NULL COMMENT '企业成员ID(admin主键ID)',
  `user_count` int(11) NOT NULL DEFAULT '0' COMMENT '被考核人数',
  `file_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件标识',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:0=禁用;1=开启',
  `delete` timestamp NULL DEFAULT NULL COMMENT '是否删除',
  `other` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '其他数据',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_assess_scheme_entid_foreign` (`entid`) USING BTREE,
  KEY `eb_enterprise_assess_scheme_user_id_index` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_assess_score`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_assess_score` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户关联企业表(admin主键)ID',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '等级名称',
  `min` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分数最小值',
  `max` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分数最大值',
  `level` tinyint(4) NOT NULL DEFAULT '0' COMMENT '级别',
  `mark` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '说明',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_assess_score_entid_index` (`entid`) USING BTREE,
  KEY `eb_enterprise_assess_score_user_id_index` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='绩效考核评分级别';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_assess_space`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_assess_space` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL,
  `assessid` int(11) NOT NULL DEFAULT '0' COMMENT '考核列表ID',
  `targetid` int(11) NOT NULL DEFAULT '0' COMMENT '考核模板ID',
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '维度名称',
  `ratio` int(11) NOT NULL DEFAULT '0' COMMENT '维度占比',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_assess_space_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='绩效考核维度表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_assess_target`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_assess_target` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `spaceid` int(11) NOT NULL DEFAULT '0' COMMENT '维度ID',
  `ratio` int(11) NOT NULL DEFAULT '0' COMMENT '权重占比',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '指标名称',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '指标内容',
  `info` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '评分等级',
  `finish_info` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '完成情况',
  `finish_ratio` int(11) NOT NULL DEFAULT '0' COMMENT '完成百分比',
  `check_info` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '上级评价',
  `max` int(11) NOT NULL DEFAULT '0' COMMENT '最高得分',
  `score` int(11) NOT NULL DEFAULT '0' COMMENT '评价得分',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='绩效考核指标表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_assess_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_assess_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `scheme_id` bigint(20) unsigned NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户关联企业表(admin主键)ID',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_assess_user_scheme_id_index` (`scheme_id`) USING BTREE,
  KEY `eb_enterprise_assess_user_user_id_index` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_assess_user_score`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_assess_user_score` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `assessid` int(11) NOT NULL DEFAULT '0' COMMENT '考核记录ID',
  `userid` int(11) NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `check_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '考核人ID',
  `test_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被考核人ID',
  `score` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '考核得分',
  `total` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '最高分',
  `grade` int(10) unsigned DEFAULT '0' COMMENT '考核等级',
  `info` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '变更说明',
  `mark` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '操作类型：0、评分；1、删除绩效；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='绩效考核评分记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_assist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_assist` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '辅助表自增id',
  `main_id` int(11) NOT NULL DEFAULT '1' COMMENT '主表ID',
  `aux_id` int(11) NOT NULL DEFAULT '1' COMMENT '副表ID',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '类型,可用其他表名区分',
  `other` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '其他数据',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_apply_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_apply_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int(11) NOT NULL COMMENT '申请人',
  `apply_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '审批申请类型：1：请假；2：补卡；3：加班；4：外出；5：出差；',
  `type_unique` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型/异常标识',
  `date_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '日期类型：1：工作日；2：休息日；3：节假日；',
  `time_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '工时类型：day：天；hour：小时；minute：分钟；',
  `calc_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '核算方式：1：调休；2：加班费；',
  `work_hours` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '加班时长',
  `apply_id` int(11) NOT NULL COMMENT '申请记录ID',
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `others` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '其他标识',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤申请记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_arrange`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_arrange` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_id` int(11) NOT NULL COMMENT '考勤组ID',
  `uid` int(11) NOT NULL COMMENT '业务员ID',
  `date` timestamp NOT NULL COMMENT '考勤时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_arrange_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_arrange_record` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `arrange_id` bigint(20) NOT NULL COMMENT '排班ID',
  `group_id` int(11) NOT NULL COMMENT '考勤组ID',
  `uid` int(11) NOT NULL COMMENT '业务员ID',
  `shift_id` int(11) NOT NULL COMMENT '班次ID',
  `date` timestamp NOT NULL COMMENT '排班日期',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤排班记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_clock_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_clock_record` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `frame_id` int(11) NOT NULL COMMENT '部门ID',
  `group_id` int(11) NOT NULL COMMENT '考勤组ID',
  `group` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '考勤组名称',
  `shift_id` int(11) NOT NULL COMMENT '考勤班次ID',
  `shift_data` varchar(511) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '班次数据',
  `address` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '打卡地址',
  `lat` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '纬度',
  `lng` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '经度',
  `remark` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
  `uid` int(11) NOT NULL COMMENT '考勤人员ID',
  `is_external` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '外勤打卡:0、考勤打卡；1、外勤打卡；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤打卡记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '考勤组名称',
  `type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '考勤类型:0、人员；1、部门；',
  `address` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '详细地址',
  `lat` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '纬度',
  `lng` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '经度',
  `effective_range` int(11) NOT NULL COMMENT '有效范围',
  `location_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '考勤地点名称',
  `repair_allowed` tinyint(3) unsigned NOT NULL COMMENT '允许补卡',
  `repair_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '补卡类型:1、缺卡;2、迟到;3、严重迟到;4、早退；',
  `is_limit_time` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '补卡时间限制:0、不限制；1、限制；',
  `limit_time` int(11) NOT NULL DEFAULT '0' COMMENT '补卡时间',
  `is_limit_number` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '补卡次数限制:0、不限制；1、限制；',
  `limit_number` int(11) NOT NULL DEFAULT '0' COMMENT '补卡次数',
  `is_photo` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '拍照打卡:0、不限制；1、限制；',
  `is_external` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '外勤打卡:0、不限制；1、限制；',
  `is_external_note` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '外勤打卡备注:0、不限制；1、限制；',
  `is_external_photo` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '外勤打卡拍照:0、不限制；1、限制；',
  `uid` int(11) NOT NULL COMMENT '业务员ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤组记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_group_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_group_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_id` int(11) NOT NULL COMMENT '考勤组ID',
  `member` int(11) NOT NULL COMMENT '考勤类型ID',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '考勤成员类型:0、考勤人员；1、无需考勤人员；2、考勤组负责人；3、考勤部门；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤组人员关联表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_group_shift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_group_shift` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_id` int(10) unsigned NOT NULL COMMENT '考勤组ID',
  `shift_id` int(10) unsigned NOT NULL COMMENT '班次表ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤组班次表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_handle_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_handle_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `statistics_id` bigint(20) NOT NULL COMMENT '统计ID',
  `shift_number` tinyint(4) NOT NULL COMMENT '班次编号',
  `before_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '修改前状态',
  `before_location_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '修改前外勤状态',
  `after_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '修改后状态',
  `after_location_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '修改后外勤状态',
  `result` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '打卡结果',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `source` tinyint(4) NOT NULL DEFAULT '0' COMMENT '来源：0、手动修改；1、补卡申请；',
  `uid` int(11) NOT NULL COMMENT '操作人',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤状态变更记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_remind`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_remind` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `shift_id` int(11) NOT NULL COMMENT '班次ID',
  `shift_num` tinyint(4) NOT NULL DEFAULT '0' COMMENT '打卡班次数量',
  `one_shift_time` timestamp NULL DEFAULT NULL COMMENT '一班次上班时间',
  `one_shift_remind` timestamp NULL DEFAULT NULL COMMENT '一班次上班提醒',
  `one_shift_remind_push` tinyint(4) NOT NULL DEFAULT '0' COMMENT '一班次上班是否推送',
  `one_shift_remind_short` timestamp NULL DEFAULT NULL COMMENT '一班次上班缺卡提醒',
  `two_shift_time` timestamp NULL DEFAULT NULL COMMENT '一班次下班时间',
  `two_shift_remind` timestamp NULL DEFAULT NULL COMMENT '一班次下班提醒',
  `two_shift_remind_push` tinyint(4) NOT NULL DEFAULT '0' COMMENT '一班次下班是否推送',
  `two_shift_remind_short` timestamp NULL DEFAULT NULL COMMENT '一班次下班缺卡提醒',
  `three_shift_time` timestamp NULL DEFAULT NULL COMMENT '二班次上班时间',
  `three_shift_remind` timestamp NULL DEFAULT NULL COMMENT '二班次上班提醒',
  `three_shift_remind_push` tinyint(4) NOT NULL DEFAULT '0' COMMENT '二班次上班是否推送',
  `three_shift_remind_short` timestamp NULL DEFAULT NULL COMMENT '二班次上班缺卡提醒',
  `four_shift_time` timestamp NULL DEFAULT NULL COMMENT '二班次下班时间',
  `four_shift_remind` timestamp NULL DEFAULT NULL COMMENT '二班次下班提醒',
  `four_shift_remind_push` tinyint(4) NOT NULL DEFAULT '0' COMMENT '二班次下班是否推送',
  `four_shift_remind_short` timestamp NULL DEFAULT NULL COMMENT '二班次下班缺卡提醒',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤提醒记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_shift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_shift` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '班次名称',
  `number` tinyint(4) NOT NULL DEFAULT '1' COMMENT '上下班次数 0、休息；1、1次上下班；2、2次上下班；',
  `rest_time` tinyint(4) NOT NULL DEFAULT '0' COMMENT '中途休息：1、开启；0、关闭；',
  `rest_start` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '休息开始时间',
  `rest_end` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '休息结束时间',
  `rest_start_after` tinyint(4) NOT NULL DEFAULT '0' COMMENT '休息开始规则 0、当日；1、次日；',
  `rest_end_after` tinyint(4) NOT NULL DEFAULT '0' COMMENT '休息结束规则 0、当日；1、次日；',
  `overtime` int(11) NOT NULL DEFAULT '0' COMMENT '加班起算时间',
  `work_time` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '工作时长',
  `color` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '颜色标识',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `uid` int(11) NOT NULL COMMENT '业务员ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤排班信息表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_shift_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_shift_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `shift_id` int(11) NOT NULL COMMENT '班次表ID',
  `number` tinyint(4) NOT NULL DEFAULT '1' COMMENT '次数 1、1次上下班；2、2次上下班；',
  `first_day_after` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上班当日次数 0、当日；1、次日；',
  `second_day_after` tinyint(4) NOT NULL DEFAULT '0' COMMENT '下班当日次数 0、当日；1、次日；',
  `work_hours` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上班时间',
  `late` int(11) NOT NULL DEFAULT '0' COMMENT '迟到',
  `extreme_late` int(11) NOT NULL DEFAULT '0' COMMENT '严重迟到',
  `late_lack_card` int(11) NOT NULL DEFAULT '0' COMMENT '晚到缺卡',
  `early_card` int(11) NOT NULL DEFAULT '0' COMMENT '提前打卡',
  `off_hours` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '下班时间',
  `early_leave` int(11) NOT NULL DEFAULT '0' COMMENT '早退',
  `early_lack_card` int(11) NOT NULL DEFAULT '0' COMMENT '提前缺卡',
  `delay_card` int(11) NOT NULL DEFAULT '0' COMMENT '延后打卡',
  `free_clock` tinyint(4) NOT NULL DEFAULT '0' COMMENT '下班可免打卡',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤排班规则表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_short_remind`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_short_remind` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `shift_id` int(11) NOT NULL COMMENT '班次ID',
  `uid` int(11) NOT NULL COMMENT '员工ID',
  `short_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '提醒类型：0、上班；1、下班；',
  `work_time` timestamp NULL DEFAULT NULL COMMENT '上班时间',
  `remind_time` timestamp NULL DEFAULT NULL COMMENT '推送时间',
  `is_push` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否推送',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤缺卡提醒表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_statistics` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int(11) NOT NULL COMMENT '考勤人员ID',
  `frame_id` int(11) NOT NULL COMMENT '部门ID',
  `group_id` int(11) NOT NULL COMMENT '考勤组ID',
  `group` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '考勤组名称',
  `shift_id` int(11) NOT NULL COMMENT '考勤班次ID',
  `shift_data` varchar(1023) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '班次数据',
  `one_shift_time` timestamp NULL DEFAULT NULL COMMENT '一班次上班打卡时间',
  `one_shift_is_after` tinyint(4) NOT NULL COMMENT '当日次数：0、当日；1、次日；',
  `one_shift_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '打卡状态：0、无需打卡；1、正常；2、迟到；3、严重迟到；4、早退；5、缺卡；',
  `one_shift_location_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '地点状态:0、正常；1、外勤；2、地点异常；',
  `one_shift_record_id` bigint(20) NOT NULL COMMENT '打卡记录ID',
  `two_shift_time` timestamp NULL DEFAULT NULL COMMENT '一班次下班打卡时间',
  `two_shift_is_after` tinyint(4) NOT NULL,
  `two_shift_status` tinyint(4) NOT NULL DEFAULT '0',
  `two_shift_location_status` tinyint(4) NOT NULL DEFAULT '0',
  `two_shift_record_id` bigint(20) NOT NULL,
  `three_shift_time` timestamp NULL DEFAULT NULL COMMENT '二班次上班打卡时间',
  `three_shift_is_after` tinyint(4) NOT NULL,
  `three_shift_status` tinyint(4) NOT NULL DEFAULT '0',
  `three_shift_location_status` tinyint(4) NOT NULL DEFAULT '0',
  `three_shift_record_id` bigint(20) NOT NULL,
  `four_shift_time` timestamp NULL DEFAULT NULL COMMENT '二班次下班打卡时间',
  `four_shift_is_after` tinyint(4) NOT NULL,
  `four_shift_status` tinyint(4) NOT NULL DEFAULT '0',
  `four_shift_location_status` tinyint(4) NOT NULL DEFAULT '0',
  `four_shift_record_id` bigint(20) NOT NULL,
  `required_work_hours` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '应出勤工时',
  `actual_work_hours` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '实际出勤工时',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤日统计表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_statistics_leave`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_statistics_leave` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `statistics_id` bigint(20) NOT NULL COMMENT '考勤记录ID',
  `apply_record_id` int(11) NOT NULL COMMENT '申请记录ID',
  `uid` int(11) NOT NULL COMMENT '考勤人员ID',
  `type_unique` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请假类型',
  `leave_duration` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '请假工时',
  `holiday_type_id` int(11) NOT NULL DEFAULT '0' COMMENT '假期类型ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤假期统计表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_attendance_whitelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_attendance_whitelist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int(11) NOT NULL COMMENT '业务员ID',
  `type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '人员类型:0、人员；1、管理员；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤白名单表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_biaozhunbandingdan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_biaozhunbandingdan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户id',
  `update_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `owner_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属用户id',
  `dingdanbianhao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '订单编号',
  `shifujine` decimal(10,2) DEFAULT NULL COMMENT '实付金额',
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dingdanbianhao` (`dingdanbianhao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_bill_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_bill_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '级别',
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `cate_no` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类编号',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级ID',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '类型:0,支出;1,收入',
  `contact id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_bill_category_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='财务分类表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_bill_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_bill_list` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建成员ID',
  `uid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '创建成员ID',
  `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '财务流水分类ID',
  `num` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '变动金额',
  `edit_time` timestamp NULL DEFAULT NULL COMMENT '变动时间',
  `types` tinyint(4) NOT NULL DEFAULT '0' COMMENT '变动类型:1=收入,0=支出',
  `type_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付方式ID',
  `pay_type` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `mark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联ID',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `link_cate` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关联类型',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_bill_list_entid_foreign` (`entid`) USING BTREE,
  KEY `eb_enterprise_bill_list_user_id_index` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='财务流水表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_calendar_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_calendar_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `day` char(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '日期',
  `is_rest` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否休息 0、上班；1、休息；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤日历配置表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类自增id',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级ID',
  `cate_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `pic` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `is_show` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '等级',
  `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类类型',
  `keyword` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标记词',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '平台编号；0、总后台；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `m_type` (`type`,`entid`) USING BTREE,
  KEY `show_cate` (`id`,`is_show`) USING BTREE,
  KEY `type_cate` (`id`,`type`,`level`,`is_show`) USING BTREE,
  KEY `eb_category_pid_index` (`pid`) USING BTREE,
  KEY `eb_category_type_index` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='公共分类表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_chanpinguanli`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_chanpinguanli` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ceshi_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品管理关联id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户id',
  `update_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `owner_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属用户id',
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_chat_app_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_chat_app_auth` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  PRIMARY KEY (`id`),
  KEY `eb_chat_app_auth_user_id_app_id_index` (`user_id`,`app_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI应用成员关联表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_chat_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_chat_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `pic` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `info` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '简介',
  `edit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '编辑权限',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户ID',
  `status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `auth_ids` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '成员ID',
  `use_limit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用频次',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `models_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模型ID',
  `count_number` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对话轮数',
  `tables` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据库表名',
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据库内容',
  `tooltip_text` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '提示词',
  `prologue_text` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '开场白',
  `prologue_list` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '开场白问题',
  `json` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '高级设置',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_table` tinyint(1) NOT NULL DEFAULT '0',
  `keyword` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '关键字',
  `data_arrange_text` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '整理数据规格',
  PRIMARY KEY (`id`),
  KEY `eb_chat_applications_uid_name_index` (`uid`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI应用表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_chat_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_chat_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `chat_application_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用id',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `top_up` timestamp NULL DEFAULT NULL,
  `is_show` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `eb_chat_history_user_id_index` (`user_id`),
  KEY `eb_chat_history_chat_application_id_index` (`chat_application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI会话历史表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_chat_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_chat_models` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户ID',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '模型名称',
  `pic` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `provider` int(11) NOT NULL DEFAULT '0' COMMENT '供应商类型',
  `models_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '模型类型',
  `is_model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '基础模型',
  `url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'API URL',
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'API KEY',
  `json` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '高级设置',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eb_chat_models_uid_name_index` (`uid`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI模型配置表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_chat_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_chat_record` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `chat_record_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'uuid',
  `chat_history_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '记录对话历史主键id',
  `vote_status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '赞扬状态',
  `problem_text` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '提问内容',
  `answer_text` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '回答内容',
  `sql_text` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'sql内容',
  `prompt_tokens` int(11) NOT NULL DEFAULT '0' COMMENT '问题tokens数',
  `completion_tokens` int(11) NOT NULL DEFAULT '0' COMMENT '回答tokens数',
  `tokens` int(11) NOT NULL DEFAULT '0' COMMENT '总tokens数',
  `details` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '详情',
  `run_time` int(11) NOT NULL DEFAULT '0' COMMENT '运行时间记录',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示1=展示',
  `chat_applications_id` int(11) NOT NULL DEFAULT '0' COMMENT 'chat_applications_id',
  PRIMARY KEY (`id`),
  KEY `eb_chat_record_chat_record_uuid_index` (`chat_record_uuid`),
  KEY `eb_chat_record_chat_history_id_index` (`chat_history_id`),
  KEY `eb_chat_record_vote_status_index` (`vote_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI会话内容表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_bill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_bill` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `eid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户ID',
  `cid` int(11) NOT NULL DEFAULT '0' COMMENT '合同ID',
  `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '续费类型ID',
  `bill_cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '续费类型ID',
  `bill_types` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '类型:0,支出;1,收入',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户ID',
  `invoice_id` int(11) NOT NULL DEFAULT '0' COMMENT '发票ID',
  `num` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `mark` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
  `types` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型：0，合同；1，续费；',
  `type_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付方式ID',
  `pay_type` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `date` timestamp NULL DEFAULT NULL COMMENT '收款日期',
  `end_date` timestamp NULL DEFAULT NULL COMMENT '续费结束日期',
  `bill_no` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '付款单号',
  `apply_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联申请审批ID',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型：0，待审核；1，已通过；2，未通过',
  `fail_msg` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '失败原因',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户资金记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_bill_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_bill_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `bill_list_id` int(11) NOT NULL DEFAULT '0' COMMENT '付款流水ID',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '操作类型',
  `operation` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '日志内容',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_client_bill_list_log_entid_index` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户资金变更记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级ID',
  `types` tinyint(4) NOT NULL DEFAULT '0' COMMENT '变动类型:1=收入,0=支出',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_client_category_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_contract`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_contract` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '业务员ID',
  `eid` int(11) NOT NULL DEFAULT '0' COMMENT '客户ID',
  `category_id` int(11) NOT NULL DEFAULT '0' COMMENT '合同分类ID',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '合同名称',
  `contract_no` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '合同编号',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '合同金额',
  `received` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '回款金额',
  `surplus` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '尾款金额',
  `start_date` date DEFAULT NULL COMMENT '合同开始时间',
  `end_date` date DEFAULT NULL COMMENT '合同结束时间',
  `mark` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '备注内容',
  `renew` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否有续费',
  `follow` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否关注',
  `up_follow` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '上级是否关注',
  `creator` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '创建人ID',
  `is_abnormal` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否异常：1、是；0、否；',
  `sign_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '签约状态：0：未签约；1：已签约；2：作废；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_contract_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_contract_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级ID',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  `level` tinyint(4) NOT NULL DEFAULT '1' COMMENT '级别',
  `entid` bigint(20) unsigned NOT NULL,
  `bill_cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '账目分类ID',
  `bill_cate_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '账目分类路径',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `cate_no` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类编号',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='合同分类';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_contract_subscribe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_contract_subscribe` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `eid` int(11) NOT NULL COMMENT '关联客户ID',
  `cid` int(11) NOT NULL COMMENT '关联合同ID',
  `subscribe_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '关注状态：0、取消关注；1、已关注；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_client_contract_subscribe_entid_index` (`entid`) USING BTREE,
  KEY `eb_enterprise_client_contract_subscribe_uid_index` (`uid`) USING BTREE,
  KEY `eb_enterprise_client_contract_subscribe_eid_index` (`eid`) USING BTREE,
  KEY `eb_enterprise_client_contract_subscribe_cid_index` (`cid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户合同关注表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '附件ID',
  `eid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户ID',
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '合同ID',
  `fid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '跟进记录ID',
  `vid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发票申请ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传用户ID',
  `name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件名称',
  `real_name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件原始名称',
  `att_dir` char(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件路径',
  `thumb_dir` char(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件压缩路径',
  `att_size` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件大小',
  `att_type` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件类型',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分后台ID',
  `up_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '上传方式：1、本地；2、七牛云；3、OSS；4、COS。',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户资料记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_follow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_follow` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `eid` int(11) NOT NULL DEFAULT '0' COMMENT '客户ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '说明内容',
  `types` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型：0，说明；1，提醒；',
  `time` timestamp NULL DEFAULT NULL COMMENT '提醒时间',
  `uniqued` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '定时任务唯一值',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态：0、待处理；1、放弃；2、已完成；',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `follow_version` int(11) NOT NULL DEFAULT '0' COMMENT '跟进版本',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户跟进记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_invoice` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `unique` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '唯一值',
  `serial_number` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '发票流水号',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '业务员ID',
  `eid` int(11) NOT NULL DEFAULT '0' COMMENT '客户ID',
  `cid` int(11) NOT NULL DEFAULT '0' COMMENT '合同ID',
  `category_id` int(11) NOT NULL DEFAULT '0' COMMENT '发票类目ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '发票名称',
  `num` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '发票编号',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '合同金额',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '发票金额',
  `types` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '发票类型',
  `title` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '发票抬头',
  `ident` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '纳税人识别号',
  `bank` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '开户行',
  `account` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '开户账号',
  `address` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '开票地址',
  `tel` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '电话',
  `collect_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮寄联系人',
  `collect_tel` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮寄联系电话',
  `collect_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮寄方式',
  `collect_email` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮寄邮箱',
  `mail_address` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮寄地址',
  `invoice_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '开票方式',
  `invoice_address` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '开票地址',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '发票状态 -1：开票撤回；0：待开票；1：已开票；2:已拒绝；3：申请作废；4:同意作废；5：拒绝作废；6：作废撤回；',
  `invalid` tinyint(4) NOT NULL DEFAULT '0' COMMENT '作废状态: 0，默认；-1，撤回；1，待审核；2，审核通过；3，审核未通过',
  `bill_date` date DEFAULT NULL COMMENT '开票日期',
  `real_date` date DEFAULT NULL COMMENT '实际开票日期',
  `mark` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '备注内容',
  `remark` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '开票备注',
  `card_remark` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '业务员备注',
  `finance_remark` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '财务备注',
  `creator` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '创建人ID',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联审批ID',
  `revoke_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '撤销申请ID',
  `link_bill` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关联付款单ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_client_invoice_unique_index` (`unique`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户发票记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_invoice_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_invoice_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `entid` bigint(20) unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类目名称',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='发票类目';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_invoice_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_invoice_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entid` int(11) NOT NULL COMMENT '企业ID',
  `invoice_id` int(11) NOT NULL COMMENT '发票ID',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `type` tinyint(4) NOT NULL COMMENT '操作类型',
  `operation` text COLLATE utf8mb4_unicode_ci COMMENT '日志内容',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_label`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_label` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标签名称',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_client_label_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_label_back`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_label_back` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标签名称',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_labels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `eid` int(11) NOT NULL DEFAULT '0' COMMENT '客户ID',
  `label_id` int(11) NOT NULL DEFAULT '0' COMMENT '标签ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_liaison`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_liaison` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户ID',
  `eid` int(11) NOT NULL DEFAULT '0' COMMENT '客户ID',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系人姓名',
  `job` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系人职务',
  `gender` int(11) NOT NULL DEFAULT '0' COMMENT '性别: 0、未知；1、男；2、女；3、其他；',
  `tel` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '电话',
  `mail` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `wechat` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信',
  `mark` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `creator` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '创建人ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_list` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '创建用户ID',
  `cid` int(11) DEFAULT '0' COMMENT '分类ID',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `client_no` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户编号',
  `name` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户名称',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系电话',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户邮箱',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户邮箱',
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户来源',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户地址',
  `detail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '地址详情',
  `follow` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否关注',
  `up_follow` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '上级是否关注',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '成交状态：0、未成交；1、已成交；',
  `creator` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '创建人ID',
  `mark` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '备注信息',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_remind`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_remind` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `eid` int(11) NOT NULL DEFAULT '0' COMMENT '客户ID',
  `cid` int(11) NOT NULL DEFAULT '0' COMMENT '合同ID',
  `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '续费类型ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `bill_id` int(11) NOT NULL DEFAULT '0' COMMENT '付款单ID',
  `num` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `mark` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '备注',
  `time` timestamp NULL DEFAULT NULL COMMENT '提醒时间',
  `this_period` timestamp NULL DEFAULT NULL COMMENT '本期时间',
  `next_period` timestamp NULL DEFAULT NULL COMMENT '下期时间',
  `uniqued` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '定时任务唯一值',
  `rate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '重复频率',
  `period` tinyint(4) NOT NULL DEFAULT '0' COMMENT '重复周期：0、天；1、周；2、月；3、年',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '类型：0、回款；1、续费；',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态：0、正常；1、放弃；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_shift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_shift` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `from` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户ID',
  `to` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '合同ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联ID',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '类型：0、客户；1、合同；2、联系人；3、发票；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_client_subscribe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_client_subscribe` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `eid` int(11) NOT NULL COMMENT '关联客户ID',
  `subscribe_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '关注状态：0、取消关注；1、已关注；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_client_subscribe_entid_index` (`entid`) USING BTREE,
  KEY `eb_enterprise_client_subscribe_uid_index` (`uid`) USING BTREE,
  KEY `eb_enterprise_client_subscribe_eid_index` (`eid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_contract`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_contract` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '业务员ID',
  `eid` int(11) NOT NULL DEFAULT '0' COMMENT '客户ID',
  `creator_uid` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `contract_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '合同名称',
  `contract_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1234' COMMENT '合同编号',
  `contract_price` decimal(10,2) NOT NULL DEFAULT '1.00' COMMENT '合同金额',
  `received` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '回款金额',
  `surplus` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '尾款金额',
  `contract_followed` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '是否关注',
  `contract_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '合同状态',
  `renew` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否有续费：0、否；1、是；',
  `start_date` date DEFAULT NULL COMMENT '开始时间',
  `end_date` date DEFAULT NULL COMMENT '结束时间',
  `signing_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '签约状态',
  `b3733f36` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `contract_category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '"[\\"25\\"]"' COMMENT '合同分类',
  `contract_cate` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '""' COMMENT '合同分类copy',
  `is_abnormal` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否异常：1、是；0、否；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `c111b844` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件',
  `cfe7a0d6` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '""' COMMENT '附件',
  `c8a7ea50` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '合同附件',
  `ca1e5ae5` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '""' COMMENT '图片',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户合同表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_contract_resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_contract_resource` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `eid` int(11) NOT NULL DEFAULT '0' COMMENT '客户ID',
  `cid` int(11) NOT NULL DEFAULT '0' COMMENT '合同ID',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '副表(admin)ID',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '备注内容',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户合同附件表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_customer` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '业务员ID',
  `before_uid` int(11) NOT NULL DEFAULT '0' COMMENT '前业务员ID',
  `creator_uid` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户名称',
  `customer_label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '""' COMMENT '客户标签',
  `customer_no` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户编号',
  `customer_way` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '"[\\"10\\"]"' COMMENT '客户来源',
  `un_followed_days` int(11) NOT NULL DEFAULT '0' COMMENT '未跟进天数',
  `amount_recorded` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '已入账金额',
  `amount_expend` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '已支出+金额',
  `invoiced_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '已开票金额',
  `contract_num` int(11) NOT NULL DEFAULT '0' COMMENT '合同数量',
  `invoice_num` int(11) NOT NULL DEFAULT '0' COMMENT '发票数量',
  `attachment_num` int(11) NOT NULL DEFAULT '0' COMMENT '附件数量',
  `return_num` int(11) NOT NULL DEFAULT '0' COMMENT '退回次数',
  `customer_followed` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '是否关注',
  `customer_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '"[\\"2\\"]"' COMMENT '客户状态',
  `area_cascade` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '"[\\"33\\",\\"3966\\",\\"3970\\"]"' COMMENT '省市区',
  `b37a3f36` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `b37a3f16` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业电话',
  `9bfe77e4` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '详细地址',
  `7763f80f` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户附件',
  `last_follow_up_time` timestamp NULL DEFAULT NULL COMMENT '最后跟进时间',
  `collect_time` timestamp NULL DEFAULT NULL COMMENT '领取时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `c839a357` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `c254fbdb` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_customer_liaison`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_customer_liaison` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '业务员ID',
  `eid` int(11) NOT NULL DEFAULT '0' COMMENT '客户ID',
  `creator_uid` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `liaison_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系人姓名',
  `liaison_tel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系电话',
  `liaison_job` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '公司职员' COMMENT '联系人职位',
  `e06d7153` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '性别',
  `e06d7152` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系人邮箱',
  `e06d7159` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系人微信',
  `l753bf282` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `cdc4d06a` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '联系人QQ',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户联系人表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_customer_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_customer_record` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `eid` int(11) NOT NULL COMMENT '客户ID',
  `type` tinyint(4) NOT NULL COMMENT '记录类型 1、退回公海；2、领取；3、流失；4、取消流失；5、移交同事；',
  `uid` int(11) NOT NULL COMMENT '业务员ID',
  `creator_uid` int(11) NOT NULL COMMENT '创建人ID',
  `record_version` int(11) NOT NULL DEFAULT '0' COMMENT '记录版本',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原因',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户信息变更表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_daily_report_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_daily_report_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `daily_id` int(11) NOT NULL COMMENT '汇报ID',
  `member` int(11) NOT NULL DEFAULT '0' COMMENT '汇报人ID(admin自增ID)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='汇报人员配置表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_dict_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_dict_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '数据名称',
  `value` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '数据值',
  `pid` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上级数据值',
  `type_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '字典类型ID',
  `type_name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '字典类型名称',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '数据层级',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `color` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '颜色',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1、开启；0、关闭；',
  `is_default` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认：1、是；0、否；',
  `mark` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注信息',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_dict_data_type_name_index` (`type_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='数据字典表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_dict_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_dict_type` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '字典名称',
  `ident` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '字典标识',
  `link_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'custom' COMMENT '关联业务',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '4' COMMENT '数据最大层级',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1、开启；0、关闭；',
  `is_default` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认：1、是；0、否；',
  `mark` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注信息',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='数据字典分类表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_dingdan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_dingdan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户id',
  `update_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `owner_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属用户id',
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_employee_train`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_employee_train` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '培训类型',
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据详情',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '企业表自增id',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '公司logo',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '管理后台标题',
  `enterprise_name` varchar(51) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '公司名称',
  `short_name` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '公司简称',
  `enterprise_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '公司编号',
  `enterprise_name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '公司名称英文',
  `lead` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '法人代表',
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '电话号',
  `phone` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `province` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '所在省',
  `city` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '所在城市',
  `area` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '所在区',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '详细地址',
  `synopsis` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '简介',
  `fax` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '传真',
  `business_license` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '营业执照',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `disable_remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '禁用备注',
  `introduction` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '简介',
  `other` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '其他',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '所属用户',
  `scale` int(11) NOT NULL DEFAULT '0' COMMENT '公司规模',
  `type` int(11) NOT NULL DEFAULT '0' COMMENT '企业类型',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '企业等级',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `verify` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=审核,1=审核通过,-1=不通过',
  `remind` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '提醒状态：0、未读；1、已读；',
  `uniqued` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业唯一值',
  `init_data` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否已加载默认数据',
  `disk_size` bigint(20) NOT NULL DEFAULT '0' COMMENT '已使用云盘空间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=禁用,1=正常,2=待缴费,3=已过期',
  `delete` timestamp NULL DEFAULT NULL COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业信息表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `key` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置字段',
  `key_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置名称',
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型(文本框,单选按钮...)',
  `input_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'input' COMMENT '表单类型',
  `category` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置分类:assess、绩效考核',
  `parameter` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则 单选框和多选框',
  `upload_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上传文件格式1单图2多图3文件',
  `required` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则',
  `width` int(11) NOT NULL DEFAULT '0' COMMENT '多行文本框的宽度',
  `high` int(11) NOT NULL DEFAULT '0' COMMENT '多行文框的高度',
  `value` varchar(5000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '默认值',
  `desc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置简介',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `entid` bigint(20) unsigned NOT NULL,
  `is_show` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_config_entid_index` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_file` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件名称',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件封面',
  `real_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件原始名称',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件夹路径',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '访问完整路径',
  `file_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件标识',
  `size` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '文件大小(单位KB)',
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件类型',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '拥有人UID',
  `edit_uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '修改人UID',
  `version` int(11) NOT NULL DEFAULT '0' COMMENT '文件版本号',
  `other` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '其他参数',
  `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类ID',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `download_count` int(11) NOT NULL DEFAULT '0' COMMENT '下载次数',
  `upload_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上传文件驱动类型1=本地,2=七牛,3=oss,4=cos',
  `is_master` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否是主文件',
  `is_template` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `delete` timestamp NULL DEFAULT NULL COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `version` (`file_id`,`version`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_file_change`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_file_change` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `file_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件标识',
  `version` int(11) NOT NULL DEFAULT '0' COMMENT '文件版本号',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id(admin自增ID)',
  `change_message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变动说明',
  `change_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_file_folder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_file_folder` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件夹名称',
  `entid` bigint(20) unsigned NOT NULL,
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级文件夹ID',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_file_folder_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_file_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_file_permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `file_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件标识',
  `uid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entid` bigint(20) unsigned NOT NULL,
  `type` enum('write','read') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_file_permissions_uid_foreign` (`uid`) USING BTREE,
  KEY `eb_enterprise_file_permissions_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_log_1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_log_1` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` varchar(36) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '用户ID',
  `user_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '管理员姓名',
  `path` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '链接',
  `method` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '访问方式',
  `event_name` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '行为',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `type` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '类型',
  `terminal` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '访问终端',
  `last_ip` varchar(45) COLLATE utf8_unicode_ci NOT NULL COMMENT '访问ip',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entid_uid` (`uid`,`entid`),
  KEY `eb_enterprise_log_1_uid_index` (`uid`),
  KEY `eb_enterprise_log_1_user_name_index` (`user_name`),
  KEY `eb_enterprise_log_1_entid_index` (`entid`),
  KEY `eb_enterprise_log_1_type_index` (`type`),
  KEY `eb_enterprise_log_1_terminal_index` (`terminal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单自增id',
  `menu_id` bigint(20) unsigned NOT NULL,
  `entid` bigint(20) unsigned NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1=权限0=菜单',
  `is_show` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否为隐藏菜单供前台使用',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '菜单状态 1=开启,0=关闭',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `is_admin` (`status`,`entid`) USING BTREE,
  KEY `eb_enterprise_menus_menu_id_foreign` (`menu_id`) USING BTREE,
  KEY `eb_enterprise_menus_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_message_notice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_message_notice` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL,
  `send_id` int(11) NOT NULL DEFAULT '0' COMMENT '发送人或者企业ID',
  `to_uid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '跳转链接',
  `uni_url` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'uni跳转路径',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '消息标题',
  `message` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '消息内容',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '消息类型:1=系统消息;0=个人消息;3=企业站内消息',
  `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '消息类型',
  `message_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息模板ID',
  `cate_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `is_read` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已读:1=已读;0=未读',
  `is_handle` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已处理',
  `is_show` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `template_type` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '消息类型',
  `button_template` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '消息类型',
  `other` text COLLATE utf8mb4_unicode_ci COMMENT '其他附加消息内容',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联记录ID',
  `link_status` int(11) NOT NULL DEFAULT '0' COMMENT '关联记录状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_message_notice_entid_foreign` (`entid`) USING BTREE,
  KEY `eb_enterprise_message_notice_to_uid_foreign` (`to_uid`) USING BTREE,
  KEY `template_type` (`template_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='消息通知记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_notice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_notice` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类ID',
  `card_id` bigint(20) unsigned NOT NULL,
  `entid` bigint(20) unsigned NOT NULL,
  `title` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '通知标题',
  `cover` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面图',
  `info` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '通知简介',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容详情',
  `is_top` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否置顶',
  `push_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '发布类型：0、立即；1、定时；',
  `push_time` timestamp NOT NULL COMMENT '发布时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `visit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览量',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `entid` (`entid`) USING BTREE,
  KEY `eb_enterprise_notice_card_id_foreign` (`card_id`) USING BTREE,
  KEY `eb_enterprise_notice_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业动态表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_notice_visit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_notice_visit` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '创建用户ID',
  `notice_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_notice_visit_uuid_index` (`user_id`) USING BTREE,
  KEY `eb_enterprise_notice_visit_notice_id_foreign` (`notice_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业动态浏览记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_paytype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_paytype` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `type_id` int(11) NOT NULL DEFAULT '0' COMMENT '支付方式ID',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `ident` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付方式标识',
  `info` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '简介',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否可用：1、是；0、否；',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业支付方式表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色自增id',
  `role_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色名称',
  `types` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '角色类型，null为用户自己添加',
  `user_count` int(11) NOT NULL DEFAULT '0' COMMENT '用户数量',
  `entid` int(11) NOT NULL COMMENT '企业ID',
  `data_level` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '数据范围：见枚举；',
  `directly` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否包含直属下级；',
  `frame_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '指定部门ID；',
  `rules` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '身份管理权限(system_menus主键ID)',
  `rule_unique` text COLLATE utf8mb4_unicode_ci COMMENT '菜单标识',
  `apis` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '身份管理接口权限(system_menus主键ID)',
  `api_unique` text COLLATE utf8mb4_unicode_ci COMMENT '接口标识',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_role_entid_index` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业角色表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_role_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '角色(enterprise_role主键)iD',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户关联企业表(admin主键)ID',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态1=开启;0=关闭',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_role_user_role_id_index` (`role_id`) USING BTREE,
  KEY `eb_enterprise_role_user_user_id_index` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业角色人员关联表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_target`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_target` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL,
  `uid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类ID',
  `name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '指标名称',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '指标内容',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '开放状态：0、不开放；1、开放；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_target_entid_foreign` (`entid`) USING BTREE,
  KEY `eb_enterprise_target_uid_foreign` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_target_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_target_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '职级类别名称',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级ID',
  `types` int(11) NOT NULL DEFAULT '0' COMMENT '类型：0、指标分类；1、指标模板分类；',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '开放状态：0、不开放；1、开放；',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_target_category_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_template` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '企业用户ID',
  `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '模板分类ID',
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '模板名称',
  `info` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板简介',
  `cover` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面图',
  `color` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#000000' COMMENT '默认字体颜色',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '开放状态：0、不开放；1、开放；',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '记分类型：0，加权评分；1，加和评分',
  `way` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '来源：0、企业端；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_template_collect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_template_collect` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '企业用户ID',
  `temp_id` int(11) NOT NULL DEFAULT '0' COMMENT '考核模板ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_user_change`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_user_change` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '人员ID',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `card_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业用户名片ID',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '变动类型：0、入职；1、转正；2、调岗；3、离职；',
  `date` date DEFAULT NULL COMMENT '变动时间',
  `new_frame` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新部门ID',
  `old_frame` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '原部门ID',
  `new_position` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新职位ID',
  `old_position` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '原职位ID',
  `info` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '原因说明',
  `mark` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联申请单ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '转移人员ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业员工人事变动表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_user_daily`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_user_daily` (
  `daily_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL DEFAULT '1',
  `uid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '副表(admin)ID',
  `finish` text COLLATE utf8mb4_unicode_ci COMMENT '工作总结',
  `plan` text COLLATE utf8mb4_unicode_ci COMMENT '工作计划',
  `mark` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '提交状态：0、未提交；1、已提交',
  `types` tinyint(4) NOT NULL DEFAULT '0' COMMENT '报告类型：0、日报；1、周报；2、月报',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`daily_id`) USING BTREE,
  KEY `eb_enterprise_user_daily_entid_foreign` (`entid`) USING BTREE,
  KEY `eb_enterprise_user_daily_uid_foreign` (`uid`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='汇报记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_user_daily_reply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_user_daily_reply` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `pid` bigint(20) unsigned NOT NULL,
  `daily_id` bigint(20) unsigned NOT NULL,
  `uid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '回复内容',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_user_daily_reply_pid_foreign` (`pid`) USING BTREE,
  KEY `eb_enterprise_user_daily_reply_daily_id_foreign` (`daily_id`) USING BTREE,
  KEY `eb_enterprise_user_daily_reply_uid_foreign` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='汇报评价表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_user_education`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_user_education` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `card_id` bigint(20) unsigned NOT NULL COMMENT '企业用户信息(enterprise_user_card)ID',
  `start_time` date DEFAULT NULL COMMENT '开始时间',
  `end_time` date DEFAULT NULL COMMENT '结束时间',
  `school_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学校名称',
  `major` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '所学专业',
  `education` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学历',
  `academic` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学位',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_user_education_card_id_foreign` (`card_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业员工任职经历表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_user_job_analysis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_user_job_analysis` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `data` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分析内容',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_user_job_analysis_entid_index` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='岗位职责表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_user_position`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_user_position` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `card_id` bigint(20) unsigned NOT NULL COMMENT '企业用户信息(enterprise_user_card)ID',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '开始时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '结束时间',
  `position` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '职位',
  `department` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '部门',
  `is_admin` tinyint(4) NOT NULL DEFAULT '0' COMMENT '身份0=普通员工;1=主管',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '任职状态0=离职;1=任职',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_user_position_card_id_foreign` (`card_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_user_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '权限自增id',
  `user_id` int(11) NOT NULL COMMENT '企业成员ID(admin主键ID)',
  `rules` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '身份管理权限(system_menus主键ID)',
  `apis` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '身份管理接口权限(system_menus主键ID)',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_user_role_user_id_index` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_user_salary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_user_salary` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `card_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业用户名片ID',
  `total` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '变更内容',
  `take_date` date DEFAULT NULL COMMENT '生效时间',
  `content` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变更内容',
  `mark` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变更原因',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联申请单ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_user_scope`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_user_scope` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联ID',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0、组织架构；1、用户；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_enterprise_user_work`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_enterprise_user_work` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `card_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '企业用户信息(enterprise_user_card)ID',
  `start_time` date DEFAULT NULL COMMENT '开始时间',
  `end_time` date DEFAULT NULL COMMENT '结束时间',
  `company` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '所在公司',
  `position` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '职位',
  `describe` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '工作描述',
  `quit_reason` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '离职原因',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_user_work_card_id_foreign` (`card_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_faguohou`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_faguohou` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户id',
  `update_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `owner_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属用户id',
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `eb_failed_jobs_uuid_unique` (`uuid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_folder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_folder` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件 id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0:文件 1:目录',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件名称',
  `path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件路径',
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '父级文件 id',
  `uid` char(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户 id',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件真实名称',
  `file_ext` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '文件后缀',
  `file_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件 url',
  `file_sn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件编号',
  `file_size` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件大小',
  `file_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件类型',
  `upload_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entid` bigint(20) unsigned DEFAULT '0' COMMENT '企业 id',
  `download_count` int(10) unsigned DEFAULT '0' COMMENT '下载次数',
  `version` int(10) unsigned DEFAULT '1' COMMENT '文件版本',
  `is_temp` tinyint(3) unsigned DEFAULT '0' COMMENT '临时文件',
  `is_share` tinyint(3) unsigned DEFAULT '0' COMMENT '是否共享',
  `is_collect` tinyint(3) unsigned DEFAULT '0' COMMENT '是否收藏',
  `is_shortcut` tinyint(3) unsigned DEFAULT '0' COMMENT '是否常用',
  `is_del` tinyint(3) unsigned DEFAULT '0' COMMENT '是否删除',
  `del_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除用户id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='云盘记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_folder_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_folder_auth` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '文件权限 id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `folder_id` bigint(20) unsigned NOT NULL COMMENT '文件 id',
  `uid` char(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户 id',
  `create` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '目录管理权限',
  `read` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '查看权限',
  `update` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '编辑权限',
  `download` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '下载权限',
  `delete` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '删除权限',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='云盘权限表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_folder_collaborate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_folder_collaborate` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `uid` int(11) NOT NULL COMMENT '业务员ID',
  `folder_id` bigint(20) unsigned NOT NULL COMMENT '文件ID',
  `update` tinyint(4) NOT NULL DEFAULT '0' COMMENT '更新权限',
  `uniqued` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '校验码',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_folder_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_folder_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '历史记录 id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `entid` bigint(20) unsigned DEFAULT '0' COMMENT '企业 id',
  `uid` char(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '修改用户',
  `folder_id` bigint(20) unsigned NOT NULL COMMENT '文件 id',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件真实名称',
  `file_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件 url',
  `file_size` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件大小',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '文件版本',
  `download_count` int(10) unsigned DEFAULT '0' COMMENT '下载次数',
  `upload_type` tinyint(4) NOT NULL COMMENT '上传方式',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_folder_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_folder_share` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '共享 id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `folder_id` bigint(20) unsigned NOT NULL COMMENT '文件 id',
  `auth_id` bigint(20) unsigned NOT NULL COMMENT '权限 id',
  `to_uid` char(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '共享用户',
  `entid` bigint(20) unsigned DEFAULT '0' COMMENT '企业 id/用户 id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '共享时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='云盘分享表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_folder_view_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_folder_view_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `uid` char(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '修改用户',
  `folder_id` bigint(20) unsigned NOT NULL COMMENT '文件 id',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件真实名称',
  `file_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件 url',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_folder_view_hitory_uid_index` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_form_cate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_form_cate` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分组名称',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分组排序',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '分组类型：1、客户；2、合同；3、联系人；',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1、显示；0、隐藏；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户设置分组记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_form_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_form_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '字段唯一值',
  `key_name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字段名称',
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型(文本框,单选按钮...)',
  `input_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'input' COMMENT '表单类型',
  `cate_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '配置分类id',
  `param` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则 单选框和多选框',
  `decimal_place` tinyint(4) NOT NULL DEFAULT '0' COMMENT '数字字段小数位数',
  `upload_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上传文件格式1单图2多图3文件',
  `required` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否必填：1、必填；0、非必填；',
  `placeholder` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '提示文字',
  `max` int(11) NOT NULL DEFAULT '0' COMMENT '最大边界值',
  `min` int(11) NOT NULL DEFAULT '0' COMMENT '最小边界值',
  `dict_ident` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '字典标识',
  `value` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '默认值',
  `uniqued` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否校验唯一',
  `desc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置简介',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：1、显示；2、隐藏；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户设置表单记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_frame`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_frame` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '部门主管ID',
  `entid` bigint(20) unsigned NOT NULL,
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级ID',
  `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '角色ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '部门名称',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  `introduce` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '部门介绍',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `user_count` int(11) NOT NULL DEFAULT '0' COMMENT '用户数量',
  `user_single_count` int(11) NOT NULL DEFAULT '0' COMMENT '单个部门总人数',
  `is_show` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '等级',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `show_cate` (`id`,`is_show`) USING BTREE,
  KEY `eb_enterprise_frame_entid_index` (`entid`) USING BTREE,
  KEY `eb_enterprise_frame_pid_index` (`pid`) USING BTREE,
  KEY `path` (`path`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='组织架构表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_frame_assist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_frame_assist` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '辅助表自增id',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '主表(enterprise_frame)ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '副表(admin)ID',
  `is_mastart` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为主部门',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为该部门的主管',
  `superior_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级主管用户ID',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '添加时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `frame_id` (`frame_id`,`user_id`) USING BTREE,
  KEY `eb_enterprise_frame_assist_frame_id_index` (`frame_id`) USING BTREE,
  KEY `eb_enterprise_frame_assist_user_id_index` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='组织架构人员关联表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_gongheguoha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_gongheguoha` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bill_category_id` int(11) NOT NULL DEFAULT '0' COMMENT '共和国哈关联id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户id',
  `update_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `owner_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属用户id',
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_gongzitiaojiegou`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_gongzitiaojiegou` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户id',
  `update_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `owner_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属用户id',
  `jibengongzi` decimal(10,2) DEFAULT NULL COMMENT '基本工资',
  `jixiaogongzi` decimal(10,2) DEFAULT NULL COMMENT '绩效工资',
  `gangweigongzi` decimal(10,2) DEFAULT NULL COMMENT '岗位工资',
  `guanlijintie` decimal(10,2) DEFAULT NULL COMMENT '管理津贴',
  `jinengbutie` decimal(10,2) DEFAULT NULL COMMENT '技能补贴',
  `qitabutie` decimal(10,2) DEFAULT NULL COMMENT '其他补贴',
  `yuangong` int(11) DEFAULT NULL COMMENT '员工',
  `tiaoxinbeizhu` text COLLATE utf8mb4_unicode_ci COMMENT '调薪备注',
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_gongzitiaojilu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_gongzitiaojilu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户id',
  `update_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `owner_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属用户id',
  `gongzitiaojiegou` int(11) DEFAULT NULL COMMENT '工资条结构',
  `jibengongzi` decimal(10,2) DEFAULT NULL COMMENT '基本工资',
  `jixiaogongzi` decimal(10,2) DEFAULT NULL COMMENT '绩效工资',
  `gangweigongzi` decimal(10,2) DEFAULT NULL COMMENT '岗位工资',
  `guanlijintie` decimal(10,2) DEFAULT NULL COMMENT '管理津贴',
  `jinengbutie` decimal(10,2) DEFAULT NULL COMMENT '技能补贴',
  `qitabutie` decimal(10,2) DEFAULT NULL COMMENT '其他补贴',
  `yuangong` int(11) NOT NULL DEFAULT '0' COMMENT '员工',
  `tiaoxinbeizhu` text COLLATE utf8mb4_unicode_ci COMMENT '调薪备注',
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_hay_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_hay_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '评估表名称',
  `uid` int(11) NOT NULL COMMENT '业务员ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_hay_group_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_hay_group_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_id` int(10) unsigned NOT NULL COMMENT '评估表ID',
  `uid` int(11) NOT NULL COMMENT '业务员ID',
  `col1` int(11) NOT NULL COMMENT '职位',
  `col2` int(11) NOT NULL COMMENT '专业知识水平',
  `col3` int(11) NOT NULL COMMENT '管理诀窍',
  `col4` int(11) NOT NULL COMMENT '人际关系技巧',
  `col5` int(11) NOT NULL COMMENT '评分',
  `col6` int(11) NOT NULL COMMENT '思维环境',
  `col7` int(11) NOT NULL COMMENT '思维难度',
  `col8` int(11) NOT NULL COMMENT '评分',
  `col9` int(11) NOT NULL COMMENT '行动自由度',
  `col10` int(11) NOT NULL COMMENT '职务责任',
  `col11` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '职务影响结果',
  `col12` int(11) NOT NULL COMMENT '评分',
  `col13` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'α',
  `col14` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'β',
  `col15` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '岗位分数',
  `col16` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '岗位系数',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_kehuguanli`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_kehuguanli` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户id',
  `update_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `owner_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属用户id',
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_message` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业id',
  `relation_id` int(11) NOT NULL DEFAULT '0' COMMENT '总平台ID',
  `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '类型ID',
  `curl_id` int(11) NOT NULL DEFAULT '0',
  `cate_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `template_type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关联通知类型',
  `template_var` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_time` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '消息标题',
  `content` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '消息内容',
  `remind_time` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '提醒时间',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '实体id',
  `event_id` int(11) NOT NULL DEFAULT '0' COMMENT '实体的触发器id',
  `user_sub` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '用户可取消订阅',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `template_type` (`template_type`) USING BTREE,
  KEY `eb_message_crud_id_index` (`crud_id`),
  KEY `eb_message_event_id_index` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='消息内容表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_message_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_message_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级ID',
  `cate_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `pic` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `is_show` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `uni_show` tinyint(4) NOT NULL DEFAULT '1' COMMENT '移动端是否显示',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '等级',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_message_category_pid_index` (`pid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='消息分类表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_message_subscribe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_message_subscribe` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '企业用户ID',
  `message_id` text COLLATE utf8mb4_unicode_ci COMMENT '消息ID',
  `is_subscribe` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订阅/取消订阅',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_message_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_message_template` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `relation_id` int(11) NOT NULL DEFAULT '0' COMMENT '总平台ID',
  `message_id` int(11) NOT NULL DEFAULT '0' COMMENT '系统消息id',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型:0=系统消息;1=短信消息',
  `template_id` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '模板id,可以为短信模板',
  `message_title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '消息标题',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '消息图片',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '跳转标题',
  `uni_url` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '移动端跳转链接',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '开启状态:0=关闭;1=开启',
  `webhook_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'bot webhook地址',
  `relation_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '系统消息状态',
  `content_template` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '内容模板',
  `button_template` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '按钮模板',
  `push_rule` tinyint(4) NOT NULL DEFAULT '0' COMMENT '推送规则:0=即时推送;1=延迟推送',
  `minute` int(11) NOT NULL DEFAULT '0' COMMENT '几分钟后推送',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `crud_event_id` int(11) NOT NULL DEFAULT '0' COMMENT '实体内的触发器id，为0是系统的消息',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `message_id_type` (`message_id`,`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='消息模板表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_openapi_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_openapi_key` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户ID',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ak` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '对外接口AK',
  `sk` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '对外接口SK',
  `info` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1、启用；0、禁用；',
  `last_time` timestamp NULL DEFAULT NULL COMMENT '最近登录时间',
  `last_ip` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '最近登录IP',
  `auth` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '接口权限（系统）',
  `crud_auth` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '接口权限（实体）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `eb_openapi_key_ak_unique` (`ak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='对外接口秘钥表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_openapi_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_openapi_rule` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级id',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '权限名称',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=分类，1=接口',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '实体id',
  `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '请求方式',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '请求地址',
  `path_prams` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求参数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `get_prams` text COLLATE utf8mb4_unicode_ci,
  `post_prams` text COLLATE utf8mb4_unicode_ci,
  `request_data` text COLLATE utf8mb4_unicode_ci,
  `response_data` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='对外接口规则表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_openapi_rule_copy1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_openapi_rule_copy1` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级id',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '权限名称',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=分类，1=接口',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '实体id',
  `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '请求方式',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '请求地址',
  `path_prams` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求参数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `get_prams` text COLLATE utf8mb4_unicode_ci,
  `post_prams` text COLLATE utf8mb4_unicode_ci,
  `request_data` text COLLATE utf8mb4_unicode_ci,
  `response_data` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_openapi_rule_copy2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_openapi_rule_copy2` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级id',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '权限名称',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=分类，1=接口',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '实体id',
  `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '请求方式',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '请求地址',
  `path_prams` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求参数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `get_prams` text COLLATE utf8mb4_unicode_ci,
  `post_prams` text COLLATE utf8mb4_unicode_ci,
  `request_data` text COLLATE utf8mb4_unicode_ci,
  `response_data` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_program` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `ident` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '编号',
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '负责人',
  `eid` bigint(20) NOT NULL DEFAULT '0' COMMENT '关联客户',
  `cid` bigint(20) NOT NULL DEFAULT '0' COMMENT '关联合同',
  `creator_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `start_date` date DEFAULT NULL COMMENT '开始时间',
  `end_date` date DEFAULT NULL COMMENT '结束时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '项目状态：0：正常；1：暂停；2：关闭；',
  `describe` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '项目描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_program_dynamic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_program_dynamic` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `types` tinyint(4) NOT NULL DEFAULT '0' COMMENT '动态类型 1：项目；2：任务；',
  `uid` bigint(20) NOT NULL COMMENT '操作人ID',
  `operator` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作人姓名',
  `relation_id` bigint(20) NOT NULL COMMENT '操作ID',
  `action_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '动作类型 1：创建；2：修改；',
  `title` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作说明',
  `describe` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目操作记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_program_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_program_member` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `program_id` bigint(20) NOT NULL COMMENT '项目ID',
  `uid` bigint(20) NOT NULL COMMENT '项目成员',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目成员表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_program_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_program_task` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务名称',
  `ident` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务编号',
  `pid` bigint(20) NOT NULL COMMENT '父级ID',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '路径',
  `top_id` bigint(20) NOT NULL COMMENT '顶级ID',
  `level` int(11) NOT NULL COMMENT '级别',
  `program_id` bigint(20) NOT NULL COMMENT '项目ID',
  `version_id` bigint(20) NOT NULL COMMENT '版本ID',
  `creator_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '负责人',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '项目状态：0：未处理；1：进行中；2：已解决；3：已验收；4：已拒绝；',
  `priority` tinyint(4) NOT NULL DEFAULT '0' COMMENT '优先级：1：紧急；2：高；3：中；4：低；',
  `plan_start` date DEFAULT NULL COMMENT '计划开始',
  `plan_end` date DEFAULT NULL COMMENT '计划结束',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `describe` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '任务描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目任务表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_program_task_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_program_task_comment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `task_id` bigint(20) NOT NULL COMMENT '任务ID',
  `pid` bigint(20) NOT NULL COMMENT '父级ID',
  `reply_uid` bigint(20) NOT NULL COMMENT '回复评论人ID',
  `uid` bigint(20) NOT NULL COMMENT '评论人ID',
  `describe` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目任务评论表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_program_task_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_program_task_member` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `task_id` bigint(20) NOT NULL COMMENT '任务ID',
  `uid` bigint(20) NOT NULL COMMENT '项目成员',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目任务成员表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_program_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_program_version` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `program_id` bigint(20) NOT NULL COMMENT '项目ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '版本名称',
  `creator_uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目版本表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_promotion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_promotion` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '晋升名称',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1、展示; 0、关闭',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_promotion_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_promotion_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `promotion_id` int(10) unsigned NOT NULL COMMENT '晋升表ID',
  `rank` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '职级',
  `position` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '职位',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '合计',
  `benefit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '效益工资',
  `standard` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标准',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_rank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_rank` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '职级名称',
  `entid` bigint(20) unsigned NOT NULL,
  `cate_id` bigint(20) unsigned NOT NULL,
  `card_id` bigint(20) unsigned NOT NULL,
  `alias` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '职级别名',
  `info` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '职级描述',
  `number` int(11) NOT NULL DEFAULT '0' COMMENT '职级人数',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:1=开启,0=关闭',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `list` (`entid`,`cate_id`,`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='职级表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_rank_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_rank_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '职级类别名称',
  `number` int(11) NOT NULL DEFAULT '0' COMMENT '职级数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_rank_category_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='职级类别表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_rank_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_rank_job` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `entid` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '职位名称',
  `cate_id` bigint(20) unsigned NOT NULL,
  `rank_id` bigint(20) unsigned NOT NULL,
  `card_id` bigint(20) unsigned NOT NULL,
  `job_count` int(11) NOT NULL DEFAULT '0' COMMENT '岗位人数',
  `describe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '岗位描述',
  `duty` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '岗位职责',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:0=关闭;1=开启',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_rank_job_entid_foreign` (`entid`) USING BTREE,
  KEY `eb_enterprise_rank_job_cate_id_foreign` (`cate_id`) USING BTREE,
  KEY `eb_enterprise_rank_job_rank_id_foreign` (`rank_id`) USING BTREE,
  KEY `eb_enterprise_rank_job_card_id_foreign` (`card_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='职位表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_rank_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_rank_level` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` bigint(20) unsigned NOT NULL,
  `salary` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '薪资范围',
  `min_level` int(11) NOT NULL DEFAULT '0' COMMENT '职等最小值',
  `max_level` int(11) NOT NULL DEFAULT '0' COMMENT '职等最大值',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_enterprise_rank_level_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='职等表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_rank_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_rank_relation` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `level_id` bigint(20) unsigned NOT NULL,
  `cate_id` bigint(20) unsigned NOT NULL,
  `rank_id` bigint(20) unsigned NOT NULL,
  `number` int(11) NOT NULL DEFAULT '0' COMMENT '职级数',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:1=开启,0=关闭',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='职级职等关联表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_roster_cycle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_roster_cycle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_id` int(10) unsigned NOT NULL COMMENT '考勤组ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '周期名称',
  `cycle` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '周期',
  `uid` int(11) NOT NULL COMMENT '业务员ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤周期表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_roster_cycle_shift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_roster_cycle_shift` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `cycle_id` int(11) NOT NULL COMMENT '周期ID',
  `shift_id` int(11) NOT NULL COMMENT '班次ID',
  `number` int(11) NOT NULL COMMENT '周期数',
  `uid` int(11) NOT NULL COMMENT '业务员ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='考勤排班周期表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ptype` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v0` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v4` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `v5` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='RBAC权限表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_salesman_custom_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_salesman_custom_field` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `custom_type` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型',
  `field_list` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '自定义数据',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户默认字段表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_schedule` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业用户ID',
  `cid` int(11) NOT NULL DEFAULT '0' COMMENT '日程分类ID',
  `color` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '日程分类颜色',
  `title` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '日程标题',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '日程内容',
  `all_day` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否全天：1、是；0、否；',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '开始时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '结束时间',
  `period` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '重复周期：0、不重复；1、日；2、月；3、年；',
  `rate` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '重复频率',
  `days` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '重复星期/日期',
  `remind` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否提醒：1、是；0、否；',
  `fail_time` timestamp NULL DEFAULT NULL COMMENT '结束时间',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联日程ID',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联业务ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '日程状态：0、待定；1、接受；2、拒绝；3、完成',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='日程表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_schedule_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_schedule_record` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户ID',
  `schedule_id` int(11) NOT NULL DEFAULT '0' COMMENT '提醒ID',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '完成状态：1、是；0、否；',
  `remind_day` date DEFAULT NULL COMMENT '提醒日期',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='日程记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_schedule_remind`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_schedule_remind` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `sid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联日程ID',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户ID',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `types` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型：user、用户；assess、考核；',
  `content` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '待办内容',
  `mark` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `period` tinyint(4) NOT NULL DEFAULT '0' COMMENT '重复周期：0、不重复；1、日；2、月；3、年；',
  `rate` int(11) NOT NULL DEFAULT '1' COMMENT '重复频率',
  `remind_day` date DEFAULT NULL COMMENT '提醒日期',
  `remind_time` time DEFAULT NULL COMMENT '提醒时间',
  `days` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '重复星期/天',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '结束日期：0、永不结束；',
  `uniqued` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '定时任务唯一值',
  `last_time` timestamp NULL DEFAULT NULL COMMENT '上次提醒日期',
  `is_remind` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否提醒过0=无，1=有',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='日程提醒表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_schedule_reply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_schedule_reply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业用户ID',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联日程ID',
  `reply_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联评论ID',
  `to_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复指定人员ID',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '任务开始时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '任务结束时间',
  `content` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '评论内容',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='日程评价表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_schedule_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_schedule_task` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业用户ID',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联日程ID',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '任务开始时间',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '任务结束时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '日程状态：0、待定；1、接受；2、拒绝；3、完成；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='日程关联用户表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_schedule_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_schedule_type` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(11) unsigned NOT NULL,
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户UID',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `color` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '日程分类颜色',
  `info` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类简介',
  `is_public` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否公共分类',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`,`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='日程类型表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_schedule_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_schedule_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业用户ID',
  `schedule_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联日程ID',
  `is_master` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为组织人0=否，1=是',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='日程人员表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_shadifang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_shadifang` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户id',
  `update_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `owner_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属用户id',
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eb_shadifang_user_id_index` (`user_id`),
  KEY `eb_shadifang_update_user_id_index` (`update_user_id`),
  KEY `eb_shadifang_owner_user_id_index` (`owner_user_id`),
  KEY `eb_shadifang_frame_id_index` (`frame_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_shengdanjiehuodong`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_shengdanjiehuodong` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户id',
  `update_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `owner_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属用户id',
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_shibahaoshiti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_shibahaoshiti` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户id',
  `update_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `owner_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属用户id',
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_storage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_storage` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `cid` bigint(20) unsigned NOT NULL,
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `creater` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '创建用户ID',
  `name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '物资名称',
  `specs` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '物资规格',
  `factory` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '生产厂家',
  `units` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '计量单位',
  `mark` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `remark` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '重要信息',
  `stock` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '库存',
  `used` int(11) NOT NULL DEFAULT '0' COMMENT '领用数量',
  `number` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '物资编号',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '物资类型：0、消耗物资；1、固定物资；',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '物资状态：0、正常；1、已领用；3、维修中；4、已报废；	',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联记录ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='物资表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_storage_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_storage_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类自增id',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级ID',
  `cate_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '等级',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '分类类型:0、消耗物资；1、固定物资；',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `m_type` (`type`,`entid`) USING BTREE,
  KEY `type_cate` (`id`,`type`,`level`) USING BTREE,
  KEY `eb_storage_category_pid_index` (`pid`) USING BTREE,
  KEY `eb_storage_category_type_index` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='物资分类表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_storage_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_storage_record` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `operator` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作用户id',
  `storage_id` bigint(20) unsigned NOT NULL,
  `storage_type` tinyint(3) unsigned DEFAULT '0' COMMENT '物资类型',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `creater` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '创建用户ID',
  `card_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联用户ID',
  `frame_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联组织架构ID',
  `info` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作说明',
  `mark` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `price` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
  `total` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '总价',
  `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '物资数量',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '操作类型：0、入库；1、领用；2、归还；3、维修；4、报废；5、维修完成；',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '当前物资状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_storage_record_storage_id_foreign` (`storage_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='物资记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_sub_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_sub_table` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `table_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '表名',
  `sub_table_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '目前表名',
  `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '目前表名增产',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当前表数据条数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_sub_table_table_name_index` (`table_name`) USING BTREE,
  KEY `eb_sub_table_sub_table_name_index` (`sub_table_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_admin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `account` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '管理员账号',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '管理员头像',
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '管理员密码',
  `real_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '管理员姓名',
  `roles` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '管理员权限(对应权限规则表主键)',
  `last_ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '访问ip',
  `login_count` int(11) NOT NULL DEFAULT '0' COMMENT '管理员登陆次数',
  `level` tinyint(4) NOT NULL DEFAULT '1' COMMENT '管理员级别',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '管理员状态 1有效0无效',
  `is_del` timestamp NULL DEFAULT NULL COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'TOKEN',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`account`,`status`,`is_del`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_attach`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_attach` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '附件ID',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分后台ID',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传用户uid',
  `name` char(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件名称',
  `real_name` char(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件原始名称',
  `att_dir` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件路径',
  `thumb_dir` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件压缩路径',
  `att_size` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件大小',
  `att_type` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '附件类型',
  `file_ext` char(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件扩展名',
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `up_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '上传方式：1、本地；2、七牛云；3、OSS；4、COS。',
  `way` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '来源：1、总后台；2、分后台；3、用户。',
  `relation_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '模块:1、汇报；',
  `relation_id` int(11) NOT NULL DEFAULT '0' COMMENT '模块ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `entid` (`entid`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统附件表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_backup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `path` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件路径',
  `uid` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '创建用户ID',
  `version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '版本号',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_city`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_city` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `city_id` int(11) NOT NULL DEFAULT '0' COMMENT '城市ID',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '省市级别',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父级id',
  `area_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '区号',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `merger_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '合并名称',
  `lng` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '经度',
  `lat` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '纬度',
  `is_show` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否展示',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='城市信息表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `category` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '配置分类',
  `key` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置字段',
  `key_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置名称',
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型(文本框,单选按钮...)',
  `input_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'input' COMMENT '表单类型',
  `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '配置分类id',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置分类路径',
  `parameter` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则 单选框和多选框',
  `upload_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上传文件格式1单图2多图3文件',
  `required` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则',
  `width` int(11) NOT NULL DEFAULT '0' COMMENT '多行文本框的宽度',
  `high` int(11) NOT NULL DEFAULT '0' COMMENT '多行文框的高度',
  `value` varchar(5000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '默认值',
  `desc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '配置简介',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '0=总后台,1=分后台',
  `ent_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分后台',
  `is_show` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `ent_key` (`key`,`entid`) USING BTREE,
  KEY `key` (`key`,`cate_id`,`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表中文名',
  `table_name_en` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表英文名',
  `cate_ids` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类IDS',
  `info` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '说明',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '主表CRUD_ID；为空为主表',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建者ID',
  `form_fields` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '当前form选择中的字段集合',
  `is_update_form` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否允许修改表单',
  `is_update_table` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否允许修改表格',
  `show_log` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否展示日志',
  `show_comment` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否展示评论',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `comment_title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '评论标题',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_system_crud_table_name_en_index` (`table_name_en`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='低代码实体表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_approve`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_approve` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联CRUD_ID',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户ID',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '审批名称',
  `icon` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '审批图标',
  `color` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '审批图标颜色',
  `info` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '审批说明',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态：0、关闭；1、开启；',
  `types` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审批类型',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_system_crud_approve_crud_id_index` (`crud_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='低代码审批配置表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_approve_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_approve_process` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户名片ID',
  `approve_id` bigint(20) unsigned NOT NULL COMMENT '关联流程ID',
  `level` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '流程级别',
  `groups` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '分组ID',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '节点名称',
  `types` tinyint(3) unsigned NOT NULL COMMENT '节点类型：0、申请人；1、审批人；2、抄送人；3、条件；4、路由；',
  `uniqued` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '节点唯一值',
  `settype` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '审核人类型：1、指定成员；2、指定部门主管；7、连续多部门；5、申请人自己；4、申请人自选；(0、无此条件)',
  `director_order` tinyint(4) NOT NULL DEFAULT '-1' COMMENT '指定层级顺序：0、从上至下；1、从下至上；(-1、无此条件)',
  `director_level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '指定主管层级/指定终点层级：1-10；(0、无此条件)',
  `no_hander` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '当前部门无负责人时：1、上级部门负责人审批；2、为空时跳过；(0、无此条件)',
  `dep_head` text COLLATE utf8mb4_unicode_ci COMMENT '指定部门负责人',
  `self_select` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否允许自选抄送人',
  `select_range` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '可选范围：1、不限范围；2、指定成员；(0、无此条件)',
  `user_list` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '指定的成员列表',
  `select_mode` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '选人方式：1、单选；2、多选；(0、无此条件)',
  `examine_mode` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '多人审批方式：1、或签；2、会签；3、依次审批；(0、无此条件)',
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '条件优先级',
  `parent` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '节点父级唯一值',
  `is_child` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否存在子节点',
  `is_condition` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否存在条件',
  `condition_list` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件详情',
  `is_initial` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为初始数据',
  `info` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据详情',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `system_crud_approve_id_foreign` (`approve_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='低代码审批流程表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_approve_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_approve_record` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `approve_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审批申请表的主键id',
  `crud_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'crud的主键id',
  `data_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '实体表主键id',
  `event` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '触发动作：create、update、delete',
  `approve_event` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '审批动作：revoke、撤销，reject、驳回;',
  `table_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'crud的表名',
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '实体表数据',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `original_data` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '修改前主表数据',
  `original_schedule_data` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '修改前附表数据',
  `schedule_data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `eb_system_crud_approve_record_approve_id_index` (`approve_id`),
  KEY `eb_system_crud_approve_record_crud_id_index` (`crud_id`),
  KEY `eb_system_crud_approve_record_data_id_index` (`data_id`),
  KEY `eb_system_crud_approve_record_event_index` (`event`),
  KEY `eb_system_crud_approve_record_approve_event_index` (`approve_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='低代码审批记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_approve_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_approve_rule` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户ID',
  `approve_id` bigint(20) unsigned NOT NULL,
  `range` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '可见范围',
  `abnormal` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '异常处理：0、自动同意；指定处理人ID；',
  `auto` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '自动审批：0、首个节点处理，其他自动同意；1、连续审批自动同意；2、每个节点都需审批；',
  `edit` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '修改权限：0、员工不可修改固定人员；1、不可删除固定抄送人；',
  `recall` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '异常处理：1、审批通过后允许撤销；',
  `is_sign` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '是否可加签',
  `is_transfer` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '是否可转审',
  `refuse` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '被拒绝后：0、返回初始，所有人重新审批；1、跳过已通过层级；',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `curd_approve_rule_id_foreign` (`approve_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='低代码审批规则表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_cate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_cate` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='低代码应用表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_comment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户ID',
  `crud_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'crud的主键id',
  `data_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'crud的表的自增id',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论父级id',
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '评论内容',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eb_system_crud_comment_crud_id_data_id_index` (`crud_id`,`data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='低代码实体评价表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_curl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_curl` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '接口标题',
  `is_pre` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=直接请求，1=前置请求',
  `pre_url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '前置请求地址',
  `pre_method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post' COMMENT '前置请求method',
  `pre_headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '前置请求header',
  `pre_data` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '前置请求data',
  `pre_cache_time` int(11) NOT NULL DEFAULT '0' COMMENT '前置请求缓存时间',
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '请求地址',
  `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post' COMMENT '请求method',
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求header',
  `data` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求data',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_dashboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_dashboard` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户ID',
  `update_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改用户ID',
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `configure` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '布局',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态：0、关闭；1、开启；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_data_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_data_share` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `share_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据共享ID',
  `crud_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'crud的主键id',
  `data_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'crud的表的自增id',
  `is_show` tinyint(4) NOT NULL DEFAULT '0' COMMENT '可查看',
  `is_update` tinyint(4) NOT NULL DEFAULT '0' COMMENT '可修改',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '可删除',
  `user_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `eb_system_crud_data_share_crud_id_data_id_index` (`crud_id`,`data_id`),
  KEY `eb_system_crud_data_share_share_id_index` (`share_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='数据共享权限和记录';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_event` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联CRUD_ID',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '事件名称',
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '事件类型',
  `action` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '触发动作',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '优先级',
  `timer` int(11) NOT NULL DEFAULT '0' COMMENT '定时任务执行周期',
  `timer_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '周期类型:0=间隔秒数；1=间隔n分；2=间隔n小时；3=间隔n天；4=每天；5=每星期；6=每年',
  `target_crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '目标实体',
  `crud_approve_id` int(11) NOT NULL DEFAULT '0' COMMENT '实体内的审核ID',
  `curl_id` int(11) NOT NULL DEFAULT '0' COMMENT '接口管理id',
  `send_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '发送用户类型:0=内部;1=外部',
  `send_user` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '发送用户',
  `notify_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '通知类型',
  `additional_search` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '附加搜索视图信息',
  `additional_search_boolean` tinyint(4) NOT NULL DEFAULT '0' COMMENT '附加搜索条件：0=符合其一 1= 符合全部',
  `template` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板内容',
  `field_options` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '字段信息',
  `aggregate_target_search` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '聚合目标搜索',
  `aggregate_target_search_boolean` tinyint(4) NOT NULL DEFAULT '0' COMMENT '聚合目标搜索：0=符合其一 1= 符合全部',
  `aggregate_data_search` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '聚合数据搜索',
  `aggregate_data_search_boolean` tinyint(4) NOT NULL DEFAULT '0' COMMENT '聚合数据搜索：0=符合其一 1= 符合全部',
  `aggregate_data_field` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分组字段关联',
  `aggregate_field_rule` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '聚合字段规则',
  `options` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '其他信息',
  `timer_options` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '执行周期配置详情',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:0=关闭;1=开启',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `sms_template_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '短信模板id',
  `work_webhook_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业微信bot webhook地址',
  `ding_webhook_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '钉钉机器人webhook地址',
  `other_webhook_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '其他bot webhook地址',
  `update_field_options` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '更新字段相关数据',
  `sms_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '短信状态',
  `system_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '系统消息状态',
  `work_webhook_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '企业微信bot webhook状态',
  `ding_webhook_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '钉钉机器人webhook状态',
  `other_webhook_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '其他bot 状态',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_system_crud_event_crud_id_index` (`crud_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_event_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_event_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联CRUD_ID',
  `event_id` int(11) NOT NULL DEFAULT '0' COMMENT '触发器ID',
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '触发类型',
  `result` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '触发结果',
  `parameter` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '出发参数',
  `log` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '日志内容',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_system_crud_event_log_crud_id_event_id_index` (`crud_id`,`event_id`) USING BTREE,
  KEY `eb_system_crud_event_log_crud_id_index` (`crud_id`) USING BTREE,
  KEY `eb_system_crud_event_log_event_id_index` (`event_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_field` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联CRUD_ID',
  `field_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字段中文名',
  `field_name_en` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字段英文名',
  `form_value` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表单值类型',
  `field_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字段类型',
  `is_default_value_not_null` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否允许空值',
  `is_table_show_row` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否在列表中默认显示',
  `comment` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字段说明',
  `prev_field` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '前一个字段英文名',
  `data_dict_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据字典ID',
  `association_crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '一对一关联CRUD_ID',
  `is_main` tinyint(4) NOT NULL DEFAULT '0' COMMENT '主展示字段',
  `is_form` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否展示在表单中',
  `form_field_uniqid` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '表单字段唯一值',
  `association_field_names` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '一对一关联字段展示',
  `options` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '其他表单信息',
  `create_modify` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否创建时可以修改',
  `update_modify` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否修改时可以修改',
  `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否默认字段',
  `is_uniqid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=不唯一，1=唯一',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_system_crud_field_crud_id_index` (`crud_id`) USING BTREE,
  KEY `eb_system_crud_field_field_name_en_index` (`field_name_en`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_form`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_form` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联CRUD_ID',
  `version` int(11) NOT NULL DEFAULT '0' COMMENT '版本号',
  `options` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表单信息',
  `fields` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表单字段信息',
  `global_options` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '表单公共信息',
  `is_index` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否主表单',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_system_crud_form_crud_id_index` (`crud_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户ID',
  `crud_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'crud的主键id',
  `data_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'crud的表的自增id',
  `log_type` enum('create','update','delete','share_create','share_delete','share_update','transfer','approve') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'create' COMMENT '状态：create=创建；update=更新；',
  `change_field_name_en` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '修改的字段名称，可以为空',
  `before_value` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '修改之前的值',
  `after_value` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '修改之后的值',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `data_crud_id` int(10) NOT NULL COMMENT '数据的crud的主键id',
  PRIMARY KEY (`id`),
  KEY `eb_system_crud_log_crud_id_data_id_index` (`crud_id`,`data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_questionnaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_questionnaire` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '问卷调查地址',
  `unique` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '唯一值',
  `crud_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '实体的id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人的id',
  `role_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=仅企业员工可见，1=所有人',
  `invalid_time` datetime NOT NULL COMMENT '失效时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=关闭；1=开启',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `eb_system_crud_questionnaire_unique_unique` (`unique`),
  KEY `eb_system_crud_questionnaire_crud_id_index` (`crud_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='问卷调查';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联角色ID',
  `crud_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联实体ID',
  `crud_name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '关联实体名称',
  `created` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '新增权限',
  `reade` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看权限:4、全部.3、指定部门.2、当前部门.1、仅本人.0、不允许',
  `reade_frame` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '查看部门',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改权限:4、全部.3、指定部门.2、当前部门.1、仅本人.0、不允许',
  `updated_frame` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '可修改部门',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除权限:4、全部.3、指定部门.2、当前部门.1、仅本人.0、不允许',
  `deleted_frame` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '可删除部门',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `transfer` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看权限:4、全部.3、指定部门.2、当前部门.1、仅本人.0、不允许',
  `transfer_frame` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '查看部门',
  `share` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看权限:4、全部.3、指定部门.2、当前部门.1、仅本人.0、不允许',
  `share_frame` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '查看部门',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_senior_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_senior_search` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联CRUD_ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联USER_ID',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `senior_title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '高级搜索标题',
  `senior_search` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '高级搜索',
  `senior_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=个人，1=系统',
  `search_boolean` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=or，1=and',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_system_crud_senior_search_crud_id_index` (`crud_id`) USING BTREE,
  KEY `eb_system_crud_senior_search_user_id_index` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_share` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `crud_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'crud的主键id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户表的id',
  `role_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=查看，1=可查看，可编辑，2=可查看，可编辑，可删除',
  `operate_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作人的id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eb_system_crud_share_crud_id_user_id_index` (`crud_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='数据共享记录';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_table` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联CRUD_ID',
  `version` int(11) NOT NULL DEFAULT '0' COMMENT '版本号',
  `senior_search` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '高级搜索',
  `view_search` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '视图搜索',
  `show_field` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '默认展示字段搜索',
  `options` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '其他表单信息',
  `is_index` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否主配置',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_system_crud_table_crud_id_index` (`crud_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_crud_table_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_crud_table_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联CRUD_ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联USER_ID',
  `senior_search` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '高级搜索',
  `show_field` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '字段信息',
  `options` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '其他信息',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_system_crud_table_user_crud_id_user_id_index` (`crud_id`,`user_id`) USING BTREE,
  KEY `eb_system_crud_table_user_crud_id_index` (`crud_id`) USING BTREE,
  KEY `eb_system_crud_table_user_user_id_index` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类id',
  `group_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '数据字段英文名',
  `group_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据字段中文名称',
  `group_info` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '数据字段提示',
  `fields` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据组字段以及类型（json数据）',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '商家ID：0、总平台',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_system_group_cate_id_group_key_index` (`cate_id`,`group_key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_group_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_group_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '组合数据自增id',
  `group_id` int(11) NOT NULL DEFAULT '0' COMMENT '组合数据数组ID(关联system_group表id)',
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据组对应的数据值（json数据）',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '数据排序',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态 1=开启,0=关闭',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_system_group_data_group_id_index` (`group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单自增id',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级菜单ID',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '按钮图标',
  `menu_name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '按钮名',
  `api` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'api请求地址',
  `methods` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '请求方式POST GET PUT DELETE',
  `unique_auth` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '前台唯一标识',
  `menu_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '前端路由路径',
  `menu_type` int(11) NOT NULL DEFAULT '0' COMMENT '路由类型：0、系统；1、实体；',
  `crud_id` int(11) NOT NULL DEFAULT '0' COMMENT '实体id',
  `uni_path` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '移动端路径',
  `uni_img` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '移动端图标',
  `position` tinyint(4) DEFAULT '0' COMMENT '菜单位置 0=侧方1=顶部',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  `component` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '前端路径',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `other` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '其他参数',
  `sort` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '菜单归属 0=总后台',
  `type` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D' COMMENT '类型：M、菜单；B、按钮；A、接口；',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否为隐藏菜单供前台使用',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '菜单状态 1=开启,0=关闭',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `is_admin` (`status`,`entid`) USING BTREE,
  KEY `api` (`api`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_package` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '扩展包名称',
  `info` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '扩展包简介',
  `version` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '扩展包版本',
  `file` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '扩展包文件名',
  `path` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '扩展包路径',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否可用',
  `uniqued` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '校验码',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_paytype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_paytype` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `ident` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '支付方式标识',
  `info` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '简介',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否可用：1、是；0、否；',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_quick`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_quick` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题名称',
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类id',
  `pc_url` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'PC端地址',
  `uni_url` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '移动端地址',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序，数字越大越在前面',
  `types` tinyint(4) NOT NULL DEFAULT '1' COMMENT '菜单类型 0:个人菜单 1:企业菜单',
  `pc_show` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'PC端显示 0:隐藏 1:显示',
  `uni_show` tinyint(4) NOT NULL DEFAULT '0' COMMENT '移动端显示 0:隐藏 1:显示',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态 0:隐藏 1:显示',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='快捷入口表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '权限自增id',
  `role_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份管理名称',
  `rules` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '身份管理权限(system_menus主键ID)',
  `apis` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '身份管理接口权限(system_menus主键ID)',
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '超级角色类型,空表示总后台',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '0=总后台,非0为企业后台',
  `level` tinyint(4) NOT NULL DEFAULT '0' COMMENT '等级',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `uniqued` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '企业唯一值',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_system_storage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_system_storage` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `access_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'access_key',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=本地存储,2=七牛,3=oss,4=cos',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '空间名',
  `region` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '地域',
  `acl` enum('private','public-read','public-read-write') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public-read' COMMENT '权限',
  `domain` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '空间域名',
  `cdn` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'CDN加速域名',
  `cname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'CNAME值',
  `is_ssl` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=http,1=https',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `add_time` int(11) NOT NULL COMMENT '添加事件',
  `update_time` int(11) NOT NULL COMMENT '更新事件',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `access_key` (`access_key`,`type`,`is_delete`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `is_delete` (`is_delete`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='云储存';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_task` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业id，0=全局任务',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务名称',
  `period` enum('year','month','week','day','second','once') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'second' COMMENT '任务执行类型',
  `persist` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否永久执行',
  `run_count` int(11) NOT NULL DEFAULT '1' COMMENT '执行次数最少1次',
  `exe_count` int(11) NOT NULL DEFAULT '0' COMMENT '已经执行次数',
  `class_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '执行任务类名',
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '执行任务方法名',
  `interval` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '执行时间,一般为json格式',
  `end_time` datetime DEFAULT NULL COMMENT '结束时间',
  `rate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '间隔时间：n天、n月、n年、n周',
  `parameter` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '执行参数一般为json格式',
  `uniqued` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务唯一值',
  `delete` timestamp NULL DEFAULT NULL COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_task_run_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_task_run_record` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `task_id` bigint(20) unsigned NOT NULL,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '错误提示',
  `line` int(11) NOT NULL DEFAULT '0' COMMENT '错误行数',
  `files` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '错误文件',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1=执行成功;0=执行失败;-1=未执行',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_upgrade_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_upgrade_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(120) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '标题说明',
  `content` text COLLATE utf8_unicode_ci NOT NULL COMMENT '更新内容',
  `first_version` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '版本号第一位',
  `second_version` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '版本号第二位',
  `third_version` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '版本号第三位',
  `fourth_version` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '版本号第四位',
  `error_data` text COLLATE utf8_unicode_ci NOT NULL COMMENT '错误内容',
  `upgrade_time` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '升级时间',
  `package_link` varchar(256) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '备份地址',
  `data_link` varchar(256) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '数据库备份地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_assess`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_assess` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `period` tinyint(4) NOT NULL DEFAULT '0' COMMENT '周期:1=周;2=月;3=年',
  `planid` bigint(20) unsigned NOT NULL,
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '组织架构ID',
  `number` int(11) NOT NULL DEFAULT '0' COMMENT '考核批次ID',
  `check_uid` int(11) NOT NULL DEFAULT '0' COMMENT '考核用户信息表ID',
  `test_uid` int(11) NOT NULL DEFAULT '0' COMMENT '被考核用户信息表ID',
  `start_time` timestamp NULL DEFAULT NULL COMMENT '考核开始时间',
  `make_time` timestamp NULL DEFAULT NULL COMMENT '目标制定时间结束时间',
  `make_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '目标制定状态：0、未制定；1、已启用；2、草稿。',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '考核结束时间',
  `test_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '自评状态：0、未评价；1、已评价；2、草稿；',
  `check_end` timestamp NULL DEFAULT NULL COMMENT '上级评价结束时间',
  `check_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上级评价状态：0、未评价；1、已评价；2、草稿。',
  `verify_time` timestamp NULL DEFAULT NULL COMMENT '审核结束时间',
  `verify_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '审核状态：0、未审核；1、已审核；',
  `score` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '考核得分',
  `total` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '最高分',
  `grade` int(11) NOT NULL DEFAULT '0' COMMENT '考核等级',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '考核状态：0、目标制定；1、自评期；2、上级评价；3、审核期；4、结束；',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '评分方式：0、加权评分；1、加和评分',
  `intact` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '完整性：1、是；0、否',
  `is_show` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用：0、未启用；1、已启用；',
  `delete` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_card_perfect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_card_perfect` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `creator` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '邀请人ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被邀请人ID',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业ID',
  `uid` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关联用户UID',
  `card_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联企业用户名片ID',
  `uniqued` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '唯一值',
  `total` int(11) NOT NULL DEFAULT '0' COMMENT '可操作量：-1、不限',
  `used` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已使用量',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态：0、待处理；1、已通过；2、已拒绝；',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否绑定用户信息',
  `fail_time` datetime DEFAULT NULL COMMENT '失效时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_change`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_change` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户信息变动自增id',
  `uuid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户uuid(关联user表uuid)',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变动类型',
  `change_mesage` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '变动说明',
  `change_time` timestamp NULL DEFAULT NULL COMMENT '变动时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_education_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_education_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关联用户ID',
  `resume_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联简历ID',
  `start_time` date DEFAULT NULL COMMENT '开始时间',
  `end_time` date DEFAULT NULL COMMENT '结束时间',
  `school_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学校名称',
  `major` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '所学专业',
  `education` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学历',
  `academic` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学位',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_user_education_history_uid_index` (`uid`) USING BTREE,
  KEY `eb_user_education_history_resume_id_index` (`resume_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_enterprise_apply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_enterprise_apply` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '发送人或者企业ID',
  `send_uid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '送达人id或者企业',
  `frame_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '-1=待处理,0=拒绝;1=同意',
  `verify` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态：0、待审核；1、已通过；-1、拒绝；',
  `perfect_key` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邀请完善信息记录关联',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '申请时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_enterprise_invite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_enterprise_invite` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `send_uid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '生成邀请码用户uuid',
  `frame_id` int(11) NOT NULL DEFAULT '0' COMMENT '组织架构ID',
  `is_verify` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否需要企业审核：1、是；0、否；',
  `uniqued` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '链接唯一值',
  `perfect_key` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邀请完善信息记录标识',
  `fail_time` timestamp NULL DEFAULT NULL COMMENT '失效时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_memorial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_memorial` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户ID',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题名称',
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '分类ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_memorial_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_memorial_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户ID',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路径',
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '上级ID',
  `types` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '类型：0、默认；1、用户添加',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_pending`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_pending` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entid` bigint(20) unsigned NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '待办类型:1=绩效;2=日报',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '跳转路径',
  `content` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '待办内容',
  `pend_ent_time` timestamp NULL DEFAULT NULL COMMENT '待办事件结束时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:1=已处理;0=未处理',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_user_pending_uid_foreign` (`uid`) USING BTREE,
  KEY `eb_user_pending_entid_foreign` (`entid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_quick`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_quick` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `entid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业id',
  `uuid` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '用户uid',
  `pc_menu_id` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'pc端菜单Id',
  `app_menu_id` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'app端菜单Id',
  `statistics_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '统计类型',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户快捷入口';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_remind_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_remind_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `week` int(11) NOT NULL DEFAULT '0' COMMENT '当年的第几周',
  `month` int(11) NOT NULL DEFAULT '0' COMMENT '当年的第几月',
  `day` int(11) NOT NULL DEFAULT '0' COMMENT '当月的第几天',
  `year` int(11) NOT NULL DEFAULT '0' COMMENT '那一年',
  `quarter` int(11) NOT NULL DEFAULT '0' COMMENT '第几季度',
  `remind_type` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '提醒类型',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'admin表ID',
  `relation_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_user_remind_log_year_entid_user_id_remind_type_index` (`year`,`entid`,`user_id`,`remind_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_resume`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_resume` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '照片',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '员工姓名',
  `phone` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `position` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '期望职位',
  `birthday` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '生日',
  `nation` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '种族',
  `politic` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '政治面貌',
  `native` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '籍贯',
  `address` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '居住地',
  `sex` tinyint(4) NOT NULL DEFAULT '0' COMMENT '性别: 0、未知；1、男；2、女；',
  `age` tinyint(3) unsigned NOT NULL DEFAULT '18' COMMENT '年龄',
  `marriage` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '婚姻状况:0、未婚；1、已婚；',
  `is_part` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否兼职:1、是；0、否；',
  `work_years` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '工作年限',
  `spare_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '紧急联系人',
  `spare_tel` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '紧急联系电话',
  `email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `work_time` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '入职时间',
  `trial_time` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '试用时间',
  `formal_time` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '转正时间',
  `treaty_time` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '合同到期时间',
  `social_num` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '社保账户',
  `fund_num` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '公积金账户',
  `bank_num` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '银行卡账户',
  `bank_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '开户行',
  `graduate_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '毕业院校',
  `graduate_date` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '毕业时间',
  `card_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证号',
  `card_front` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证正面',
  `card_both` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '身份证背面',
  `education` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学历',
  `education_image` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学历证书',
  `acad` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学位',
  `acad_image` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '学位证书',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_user_resume_uid_foreign` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_schedule` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户ID',
  `entid` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `types` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '类型：user、用户；assess、考核；',
  `content` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '待办内容',
  `mark` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注信息',
  `remind` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否提醒：1、是；0、否；',
  `repeat` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否重复：1、是；0、否；',
  `period` tinyint(4) NOT NULL DEFAULT '0' COMMENT '重复周期：0、天；1、周；2、月；3、年',
  `rate` int(11) NOT NULL DEFAULT '1' COMMENT '重复频率',
  `remind_day` date DEFAULT NULL COMMENT '提醒日期',
  `remind_time` time DEFAULT NULL COMMENT '提醒时间',
  `days` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '重复星期/天',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '结束日期：0、用不结束；',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联ID',
  `uniqued` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '定时任务唯一值',
  `last_time` timestamp NULL DEFAULT NULL COMMENT '上次提醒日期',
  `is_remind` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否提醒过0=无，1=有',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_schedule_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_schedule_record` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户ID',
  `schedultid` int(11) NOT NULL DEFAULT '0' COMMENT '提醒ID',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '完成状态：1、是；0、否；',
  `remind_day` date DEFAULT NULL COMMENT '提醒日期',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_token` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关联用户UID',
  `client` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登陆客户端名称',
  `last_ip` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登陆IP',
  `mac` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登陆MAC地址',
  `last_token` text COLLATE utf8mb4_unicode_ci COMMENT '上次过期token',
  `remember_token` text COLLATE utf8mb4_unicode_ci COMMENT '当前登陆token',
  `fail_time` datetime DEFAULT NULL COMMENT '失效时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eb_user_work_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eb_user_work_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '关联用户ID',
  `resume_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联简历ID',
  `start_time` date DEFAULT NULL COMMENT '开始时间',
  `end_time` date DEFAULT NULL COMMENT '结束时间',
  `company` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '所在公司',
  `position` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '职位',
  `describe` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '工作描述',
  `quit_reason` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '离职原因',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `eb_user_work_history_uid_index` (`uid`) USING BTREE,
  KEY `eb_user_work_history_resume_id_index` (`resume_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `eb_migrations` VALUES (1,'2021_03_02_011722_eb_system_config',1);
INSERT INTO `eb_migrations` VALUES (2,'2021_03_02_011748_eb_system_group',1);
INSERT INTO `eb_migrations` VALUES (3,'2021_03_02_020431_eb_system_admin',1);
INSERT INTO `eb_migrations` VALUES (4,'2021_03_02_020441_eb_category',1);
INSERT INTO `eb_migrations` VALUES (5,'2021_03_10_011402_eb_system_menus',1);
INSERT INTO `eb_migrations` VALUES (6,'2021_03_10_020601_eb_system_role',1);
INSERT INTO `eb_migrations` VALUES (7,'2021_03_10_021018_eb_system_log',1);
INSERT INTO `eb_migrations` VALUES (8,'2021_03_16_063940_eb_system_group_data',1);
INSERT INTO `eb_migrations` VALUES (9,'2021_03_18_025850_eb_user',1);
INSERT INTO `eb_migrations` VALUES (10,'2021_03_18_031550_eb_enterprise',1);
INSERT INTO `eb_migrations` VALUES (11,'2021_03_18_031550_eb_enterprise_menus',1);
INSERT INTO `eb_migrations` VALUES (12,'2021_03_18_031613_eb_assist',1);
INSERT INTO `eb_migrations` VALUES (13,'2021_03_18_040221_eb_user_info',1);
INSERT INTO `eb_migrations` VALUES (14,'2021_03_18_040249_eb_user_change',1);
INSERT INTO `eb_migrations` VALUES (15,'2021_03_18_071012_eb_address_book',1);
INSERT INTO `eb_migrations` VALUES (16,'2021_03_24_030153_eb_system_attach',1);
INSERT INTO `eb_migrations` VALUES (17,'2021_04_06_033035_eb_user_enterprise',1);
INSERT INTO `eb_migrations` VALUES (18,'2021_04_06_035907_eb_enterprise_user_card',1);
INSERT INTO `eb_migrations` VALUES (19,'2021_04_07_074419_eb_enterprise_frame',1);
INSERT INTO `eb_migrations` VALUES (20,'2021_04_08_072252_eb_system_city',1);
INSERT INTO `eb_migrations` VALUES (21,'2021_04_09_064148_eb_enterprise_log',1);
INSERT INTO `eb_migrations` VALUES (22,'2021_04_12_062958_eb_enterprise_user_role',1);
INSERT INTO `eb_migrations` VALUES (23,'2021_04_15_012444_eb_enterprise_file',1);
INSERT INTO `eb_migrations` VALUES (24,'2021_04_15_032437_eb_enterprise_file_folder',1);
INSERT INTO `eb_migrations` VALUES (25,'2021_04_19_070822_eb_enterprise_user_address_book_role',1);
INSERT INTO `eb_migrations` VALUES (26,'2021_04_19_072010_eb_enterprise_message_notice',1);
INSERT INTO `eb_migrations` VALUES (27,'2021_04_19_073307_eb_user_enterprise_apply',1);
INSERT INTO `eb_migrations` VALUES (28,'2021_04_20_034414_eb_enterprise_frame_role',1);
INSERT INTO `eb_migrations` VALUES (29,'2021_04_21_042138_eb_enterprise_role',1);
INSERT INTO `eb_migrations` VALUES (30,'2021_04_21_065602_eb_enterprise_role_user',1);
INSERT INTO `eb_migrations` VALUES (31,'2021_04_21_094216_eb_enterprise_frame_assist',1);
INSERT INTO `eb_migrations` VALUES (32,'2021_04_22_022231_eb_enterprise_user_position',1);
INSERT INTO `eb_migrations` VALUES (33,'2021_04_22_022248_eb_enterprise_user_work',1);
INSERT INTO `eb_migrations` VALUES (34,'2021_04_22_022304_eb_enterprise_user_education',1);
INSERT INTO `eb_migrations` VALUES (35,'2021_04_23_082804_eb_enterprise_file_permissions',1);
INSERT INTO `eb_migrations` VALUES (36,'2021_04_23_084748_eb_enterprise_file_change',1);
INSERT INTO `eb_migrations` VALUES (37,'2021_04_25_020732_eb_enterprise_assess_scheme',1);
INSERT INTO `eb_migrations` VALUES (38,'2021_04_25_020809_eb_enterprise_assess_user',1);
INSERT INTO `eb_migrations` VALUES (39,'2021_04_25_030619_eb_enterprise_assess__compute_rule',1);
INSERT INTO `eb_migrations` VALUES (40,'2021_04_27_070002_eb_task',1);
INSERT INTO `eb_migrations` VALUES (41,'2021_04_28_033808_eb_enterprise_user_daily',1);
INSERT INTO `eb_migrations` VALUES (42,'2021_04_28_035257_eb_enterprise_user_daily_reply',1);
INSERT INTO `eb_migrations` VALUES (43,'2021_05_06_174409_eb_user_pending',1);
INSERT INTO `eb_migrations` VALUES (44,'2021_05_24_121136_eb_enterprise_rank_category',1);
INSERT INTO `eb_migrations` VALUES (45,'2021_05_24_121158_eb_enterprise_rank',1);
INSERT INTO `eb_migrations` VALUES (46,'2021_05_24_121159_eb_enterprise_rank_job',1);
INSERT INTO `eb_migrations` VALUES (47,'2021_05_25_110308_eb_enterprise_rank_level',1);
INSERT INTO `eb_migrations` VALUES (48,'2021_05_25_121152_eb_enterprise_rank_relation',1);
INSERT INTO `eb_migrations` VALUES (49,'2021_05_29_110431_eb_task_run_record',1);
INSERT INTO `eb_migrations` VALUES (50,'2021_06_11_161221_eb_enterprise_role_frame',1);
INSERT INTO `eb_migrations` VALUES (51,'2021_06_16_094720_eb_system_admin_assist',1);
INSERT INTO `eb_migrations` VALUES (52,'2021_06_19_145813_eb_enterprise_frame_superior_assist',1);
INSERT INTO `eb_migrations` VALUES (53,'2021_07_20_092908_eb_enterprise_bill_category',1);
INSERT INTO `eb_migrations` VALUES (54,'2021_07_20_093326_eb_enterprise_bill_list',1);
INSERT INTO `eb_migrations` VALUES (55,'2021_07_27_091640_eb_enterprise_assess_score',1);
INSERT INTO `eb_migrations` VALUES (56,'2021_07_27_100736_eb_enterprise_config',1);
INSERT INTO `eb_migrations` VALUES (57,'2021_07_28_094711_eb_enterprise_target_category',1);
INSERT INTO `eb_migrations` VALUES (58,'2021_07_28_095131_eb_enterprise_target',1);
INSERT INTO `eb_migrations` VALUES (59,'2021_07_29_110716_eb_enterprise_assess_plan',1);
INSERT INTO `eb_migrations` VALUES (60,'2021_07_30_094527_eb_enterprise_assess_plan_user',1);
INSERT INTO `eb_migrations` VALUES (61,'2021_08_02_151948_eb_enterprise_user_assess',1);
INSERT INTO `eb_migrations` VALUES (62,'2021_08_02_152119_eb_enterprise_assess_space',1);
INSERT INTO `eb_migrations` VALUES (63,'2021_08_02_152351_eb_enterprise_assess_target',1);
INSERT INTO `eb_migrations` VALUES (64,'2021_08_05_104426_eb_enterprise_assess_reply',1);
INSERT INTO `eb_migrations` VALUES (65,'2021_08_06_091001_eb_enterprise_template',1);
INSERT INTO `eb_migrations` VALUES (66,'2021_08_06_155856_eb_enterprise_template_collect',1);
INSERT INTO `eb_migrations` VALUES (67,'2021_08_31_162647_eb_user_enterprise_invite',1);
INSERT INTO `eb_migrations` VALUES (68,'2021_09_10_144233_eb_system_paytype',1);
INSERT INTO `eb_migrations` VALUES (69,'2021_09_10_144401_eb_enterprise_paytype',1);
INSERT INTO `eb_migrations` VALUES (70,'2021_09_13_105803_eb_user_schedule',1);
INSERT INTO `eb_migrations` VALUES (71,'2021_09_23_161754_eb_user_memorial_category',1);
INSERT INTO `eb_migrations` VALUES (72,'2021_09_23_163155_eb_user_memorial',1);
INSERT INTO `eb_migrations` VALUES (73,'2021_10_13_114014_eb_user_schedule_record',1);
INSERT INTO `eb_migrations` VALUES (74,'2021_10_15_113434_eb_enterprise_user_assess_score',1);
INSERT INTO `eb_migrations` VALUES (75,'2021_10_29_091002_eb_enterprise_client',1);
INSERT INTO `eb_migrations` VALUES (76,'2021_10_29_091042_eb_enterprise_client_label',1);
INSERT INTO `eb_migrations` VALUES (77,'2021_10_29_091121_eb_enterprise_client_contract',1);
INSERT INTO `eb_migrations` VALUES (78,'2021_10_29_091148_eb_enterprise_client_invoice',1);
INSERT INTO `eb_migrations` VALUES (79,'2021_10_29_091446_eb_enterprise_client_liaison',1);
INSERT INTO `eb_migrations` VALUES (80,'2021_11_05_172817_eb_enterprise_client_labels',1);
INSERT INTO `eb_migrations` VALUES (81,'2021_11_08_110515_eb_enterprise_client_follow',1);
INSERT INTO `eb_migrations` VALUES (82,'2021_11_08_120730_eb_enterprise_client_bill',1);
INSERT INTO `eb_migrations` VALUES (83,'2021_11_16_171526_eb_enterprise_client_file',1);
INSERT INTO `eb_migrations` VALUES (84,'2021_11_17_151537_eb_enterprise_client_remind',1);
INSERT INTO `eb_migrations` VALUES (85,'2021_11_22_101010_eb_enterprise_client_record',1);
INSERT INTO `eb_migrations` VALUES (86,'2022_01_10_153912_eb_enterprise_user_scope',1);
INSERT INTO `eb_migrations` VALUES (87,'2022_02_07_100256_eb_enterprise_approve',1);
INSERT INTO `eb_migrations` VALUES (88,'2022_02_07_102545_eb_enterprise_approve_form',1);
INSERT INTO `eb_migrations` VALUES (89,'2022_02_07_102620_eb_enterprise_approve_process',1);
INSERT INTO `eb_migrations` VALUES (90,'2022_02_07_164953_eb_enterprise_approve_rule',1);
INSERT INTO `eb_migrations` VALUES (91,'2022_02_07_170356_eb_enterprise_approve_apply',1);
INSERT INTO `eb_migrations` VALUES (92,'2022_02_07_170530_eb_enterprise_approve_user',1);
INSERT INTO `eb_migrations` VALUES (93,'2022_02_08_105108_eb_enterprise_approve_reply',1);
INSERT INTO `eb_migrations` VALUES (94,'2022_02_15_092649_eb_enterprise_approve_content',1);
INSERT INTO `eb_migrations` VALUES (95,'2022_03_22_165304_eb_system_package',1);
INSERT INTO `eb_migrations` VALUES (96,'2022_04_13_093437_eb_enterprise_notice',1);
INSERT INTO `eb_migrations` VALUES (97,'2022_04_24_150931_eb_enterprise_notice_visit',1);
INSERT INTO `eb_migrations` VALUES (98,'2022_05_09_164704_eb_storage_category',1);
INSERT INTO `eb_migrations` VALUES (99,'2022_05_09_164923_eb_storage',1);
INSERT INTO `eb_migrations` VALUES (100,'2022_05_11_181713_eb_storage_record',1);
INSERT INTO `eb_migrations` VALUES (416,'2023_02_02_115537_eb_sub_table',2);
INSERT INTO `eb_migrations` VALUES (417,'2023_02_08_095732_eb_enterprise_user_job_analysis',3);
INSERT INTO `eb_migrations` VALUES (418,'2023_09_28_153025_create_attendance_statistics_leave_table',4);
INSERT INTO `eb_migrations` VALUES (419,'2023_10_10_094704_create_approve_holiday_type_table',5);
INSERT INTO `eb_migrations` VALUES (420,'2023_10_10_170831_update_attendance_apply_record_table',5);
INSERT INTO `eb_migrations` VALUES (421,'2023_10_10_180714_update_attendance_statistics_leave_table',5);
INSERT INTO `eb_migrations` VALUES (422,'2023_10_12_174727_update_attendance_statistics_table',5);
INSERT INTO `eb_migrations` VALUES (423,'2023_10_16_161208_create_daily_report_member_table',5);
INSERT INTO `eb_migrations` VALUES (424,'2023_10_19_175648_create_message_category_table',6);
INSERT INTO `eb_migrations` VALUES (425,'2023_11_17_095128_create_dict_type_table',7);
INSERT INTO `eb_migrations` VALUES (426,'2023_11_17_095230_create_dict_data_table',7);
INSERT INTO `eb_migrations` VALUES (427,'2023_11_21_120234_create_form_cate_table',7);
INSERT INTO `eb_migrations` VALUES (428,'2023_11_21_120303_create_form_data_table',7);
INSERT INTO `eb_migrations` VALUES (429,'2023_11_21_161316_update_assess_space_table',7);
INSERT INTO `eb_migrations` VALUES (430,'2023_11_21_161326_update_assess_target_table',7);
INSERT INTO `eb_migrations` VALUES (431,'2023_11_21_161326_update_client_invoice_table',8);
INSERT INTO `eb_migrations` VALUES (432,'2023_11_27_100851_create_customer_table',8);
INSERT INTO `eb_migrations` VALUES (433,'2023_11_28_144309_create_salesman_custom_field_table',8);
INSERT INTO `eb_migrations` VALUES (434,'2023_12_11_174228_create_customer_liaison',8);
INSERT INTO `eb_migrations` VALUES (435,'2023_12_11_174617_create_contract_table',8);
INSERT INTO `eb_migrations` VALUES (436,'2023_12_20_091623_update_assess_table',8);
INSERT INTO `eb_migrations` VALUES (437,'2023_12_25_101254_update_approve_form_table',8);
INSERT INTO `eb_migrations` VALUES (438,'2023_12_25_101256_update_approve_content_table',8);
INSERT INTO `eb_migrations` VALUES (439,'2023_12_27_095544_update_client_follow_table',8);
INSERT INTO `eb_migrations` VALUES (440,'2024_01_10_174432_create_customer_record_table',8);
INSERT INTO `eb_migrations` VALUES (441,'2024_02_19_165149_create_system_crud_approve_process_table',9);
INSERT INTO `eb_migrations` VALUES (442,'2024_02_19_165149_create_system_crud_approve_rule_table',9);
INSERT INTO `eb_migrations` VALUES (443,'2024_02_26_162047_create_system_crud_table',9);
INSERT INTO `eb_migrations` VALUES (444,'2024_02_26_165638_create_system_crud_field_table',9);
INSERT INTO `eb_migrations` VALUES (445,'2024_02_26_165656_create_system_crud_event_log_table',9);
INSERT INTO `eb_migrations` VALUES (446,'2024_02_26_165656_create_system_crud_form_table',9);
INSERT INTO `eb_migrations` VALUES (447,'2024_02_26_165712_create_system_crud_table_table',9);
INSERT INTO `eb_migrations` VALUES (448,'2024_02_28_100330_create_system_crud_approve_table',9);
INSERT INTO `eb_migrations` VALUES (449,'2024_02_28_100607_create_system_crud_cate_table',9);
INSERT INTO `eb_migrations` VALUES (450,'2024_02_28_100607_create_system_crud_event_table',9);
INSERT INTO `eb_migrations` VALUES (451,'2024_02_28_100607_create_system_crud_table_user_table',9);
INSERT INTO `eb_migrations` VALUES (452,'2024_03_07_102512_create_program_table',9);
INSERT INTO `eb_migrations` VALUES (453,'2024_03_07_105011_create_program_member_table',9);
INSERT INTO `eb_migrations` VALUES (454,'2024_03_07_105822_create_program_version_table',9);
INSERT INTO `eb_migrations` VALUES (455,'2024_03_11_115925_create_system_storage_table',9);
INSERT INTO `eb_migrations` VALUES (456,'2024_03_11_163925_create_program_task_table',9);
INSERT INTO `eb_migrations` VALUES (457,'2024_03_11_170831_create_program_task_member_table',9);
INSERT INTO `eb_migrations` VALUES (458,'2024_03_18_155911_create_program_task_comment_table',9);
INSERT INTO `eb_migrations` VALUES (459,'2024_03_19_155419_create_program_dynamic_table',9);
INSERT INTO `eb_migrations` VALUES (460,'2024_03_20_091837_create_system_crud_role_table',9);
INSERT INTO `eb_migrations` VALUES (461,'2024_04_01_091529_update_approve_holiday_type_table',9);
INSERT INTO `eb_migrations` VALUES (462,'2024_04_09_101121_update_frame_table',9);
INSERT INTO `eb_migrations` VALUES (463,'2024_04_11_105145_update_approve_apply_table',9);
INSERT INTO `eb_migrations` VALUES (464,'2024_05_09_120947_update_system_menus_table',9);
INSERT INTO `eb_migrations` VALUES (465,'2024_05_13_121858_create_admin_table',10);
INSERT INTO `eb_migrations` VALUES (466,'2024_05_13_121935_create_admin_info_table',10);
INSERT INTO `eb_migrations` VALUES (467,'2024_05_14_105734_create_system_crud_dashboard_table',10);
INSERT INTO `eb_migrations` VALUES (468,'2024_05_20_163334_create_folder_view_hitory_table',10);
INSERT INTO `eb_migrations` VALUES (469,'2024_06_03_123456_update_system_menus_table',10);
INSERT INTO `eb_migrations` VALUES (470,'2024_06_03_864344_create_system_crud_senior_search',10);
INSERT INTO `eb_migrations` VALUES (471,'2024_06_13_171432_update_apprve_table',10);
INSERT INTO `eb_migrations` VALUES (472,'2024_06_15_162706_update_system_config_table',10);
INSERT INTO `eb_migrations` VALUES (473,'2024_06_19_114510_update_user_card_perfect_table',10);
INSERT INTO `eb_migrations` VALUES (474,'2024_07_11_115311_update_folder_table',11);
INSERT INTO `eb_migrations` VALUES (475,'2024_07_11_1674872_update_frame_table',12);
INSERT INTO `eb_migrations` VALUES (476,'2024_07_15_983456_update_system_crud_event_table',13);
INSERT INTO `eb_migrations` VALUES (477,'2024_07_11_864355_create_system_crud_curl',13);
INSERT INTO `eb_migrations` VALUES (478,'2024_09_13_171432_update_promotion_data_table',14);
INSERT INTO `eb_migrations` VALUES (479,'2024_09_19_102858_update_apprve_table',15);
INSERT INTO `eb_migrations` VALUES (480,'2024_09_24_160736_create_openapi_key_table',16);
INSERT INTO `eb_migrations` VALUES (481,'2024_09_29_160736_create_openapi_rule_table',16);
INSERT INTO `eb_migrations` VALUES (482,'2024_11_04_170035_create_admin_table',0);
INSERT INTO `eb_migrations` VALUES (483,'2024_11_07_144251_create_admin_table',0);
INSERT INTO `eb_migrations` VALUES (484,'2024_11_07_144251_create_admin_info_table',0);
INSERT INTO `eb_migrations` VALUES (485,'2024_11_07_144251_create_agreement_table',0);
INSERT INTO `eb_migrations` VALUES (486,'2024_11_07_144251_create_approve_table',0);
INSERT INTO `eb_migrations` VALUES (487,'2024_11_07_144251_create_approve_apply_table',0);
INSERT INTO `eb_migrations` VALUES (488,'2024_11_07_144251_create_approve_content_table',0);
INSERT INTO `eb_migrations` VALUES (489,'2024_11_07_144251_create_approve_form_table',0);
INSERT INTO `eb_migrations` VALUES (490,'2024_11_07_144251_create_approve_holiday_type_table',0);
INSERT INTO `eb_migrations` VALUES (491,'2024_11_07_144251_create_approve_process_table',0);
INSERT INTO `eb_migrations` VALUES (492,'2024_11_07_144251_create_approve_reply_table',0);
INSERT INTO `eb_migrations` VALUES (493,'2024_11_07_144251_create_approve_rule_table',0);
INSERT INTO `eb_migrations` VALUES (494,'2024_11_07_144251_create_approve_user_table',0);
INSERT INTO `eb_migrations` VALUES (495,'2024_11_07_144251_create_assess_table',0);
INSERT INTO `eb_migrations` VALUES (496,'2024_11_07_144251_create_assess_compute_rule_table',0);
INSERT INTO `eb_migrations` VALUES (497,'2024_11_07_144251_create_assess_frame_table',0);
INSERT INTO `eb_migrations` VALUES (498,'2024_11_07_144251_create_assess_plan_table',0);
INSERT INTO `eb_migrations` VALUES (499,'2024_11_07_144251_create_assess_plan_user_table',0);
INSERT INTO `eb_migrations` VALUES (500,'2024_11_07_144251_create_assess_reply_table',0);
INSERT INTO `eb_migrations` VALUES (501,'2024_11_07_144251_create_assess_scheme_table',0);
INSERT INTO `eb_migrations` VALUES (502,'2024_11_07_144251_create_assess_score_table',0);
INSERT INTO `eb_migrations` VALUES (503,'2024_11_07_144251_create_assess_space_table',0);
INSERT INTO `eb_migrations` VALUES (504,'2024_11_07_144251_create_assess_target_table',0);
INSERT INTO `eb_migrations` VALUES (505,'2024_11_07_144251_create_assess_user_table',0);
INSERT INTO `eb_migrations` VALUES (506,'2024_11_07_144251_create_assess_user_score_table',0);
INSERT INTO `eb_migrations` VALUES (507,'2024_11_07_144251_create_assist_table',0);
INSERT INTO `eb_migrations` VALUES (508,'2024_11_07_144251_create_attendance_apply_record_table',0);
INSERT INTO `eb_migrations` VALUES (509,'2024_11_07_144251_create_attendance_arrange_table',0);
INSERT INTO `eb_migrations` VALUES (510,'2024_11_07_144251_create_attendance_arrange_record_table',0);
INSERT INTO `eb_migrations` VALUES (511,'2024_11_07_144251_create_attendance_clock_record_table',0);
INSERT INTO `eb_migrations` VALUES (512,'2024_11_07_144251_create_attendance_group_table',0);
INSERT INTO `eb_migrations` VALUES (513,'2024_11_07_144251_create_attendance_group_member_table',0);
INSERT INTO `eb_migrations` VALUES (514,'2024_11_07_144251_create_attendance_group_shift_table',0);
INSERT INTO `eb_migrations` VALUES (515,'2024_11_07_144251_create_attendance_handle_record_table',0);
INSERT INTO `eb_migrations` VALUES (516,'2024_11_07_144251_create_attendance_remind_table',0);
INSERT INTO `eb_migrations` VALUES (517,'2024_11_07_144251_create_attendance_shift_table',0);
INSERT INTO `eb_migrations` VALUES (518,'2024_11_07_144251_create_attendance_shift_rule_table',0);
INSERT INTO `eb_migrations` VALUES (519,'2024_11_07_144251_create_attendance_short_remind_table',0);
INSERT INTO `eb_migrations` VALUES (520,'2024_11_07_144251_create_attendance_statistics_table',0);
INSERT INTO `eb_migrations` VALUES (521,'2024_11_07_144251_create_attendance_statistics_leave_table',0);
INSERT INTO `eb_migrations` VALUES (522,'2024_11_07_144251_create_attendance_whitelist_table',0);
INSERT INTO `eb_migrations` VALUES (523,'2024_11_07_144251_create_biaozhunbandingdan_table',0);
INSERT INTO `eb_migrations` VALUES (524,'2024_11_07_144251_create_bill_category_table',0);
INSERT INTO `eb_migrations` VALUES (525,'2024_11_07_144251_create_bill_list_table',0);
INSERT INTO `eb_migrations` VALUES (526,'2024_11_07_144251_create_calendar_config_table',0);
INSERT INTO `eb_migrations` VALUES (527,'2024_11_07_144251_create_category_table',0);
INSERT INTO `eb_migrations` VALUES (530,'2024_11_07_144251_create_chanpinguanli_table',0);
INSERT INTO `eb_migrations` VALUES (533,'2024_11_07_144251_create_client_bill_table',0);
INSERT INTO `eb_migrations` VALUES (534,'2024_11_07_144251_create_client_bill_log_table',0);
INSERT INTO `eb_migrations` VALUES (535,'2024_11_07_144251_create_client_category_table',0);
INSERT INTO `eb_migrations` VALUES (536,'2024_11_07_144251_create_client_contract_table',0);
INSERT INTO `eb_migrations` VALUES (537,'2024_11_07_144251_create_client_contract_category_table',0);
INSERT INTO `eb_migrations` VALUES (538,'2024_11_07_144251_create_client_contract_subscribe_table',0);
INSERT INTO `eb_migrations` VALUES (539,'2024_11_07_144251_create_client_file_table',0);
INSERT INTO `eb_migrations` VALUES (540,'2024_11_07_144251_create_client_follow_table',0);
INSERT INTO `eb_migrations` VALUES (541,'2024_11_07_144251_create_client_invoice_table',0);
INSERT INTO `eb_migrations` VALUES (542,'2024_11_07_144251_create_client_invoice_category_table',0);
INSERT INTO `eb_migrations` VALUES (543,'2024_11_07_144251_create_client_invoice_log_table',0);
INSERT INTO `eb_migrations` VALUES (544,'2024_11_07_144251_create_client_label_table',0);
INSERT INTO `eb_migrations` VALUES (545,'2024_11_07_144251_create_client_label_back_table',0);
INSERT INTO `eb_migrations` VALUES (546,'2024_11_07_144251_create_client_labels_table',0);
INSERT INTO `eb_migrations` VALUES (547,'2024_11_07_144251_create_client_liaison_table',0);
INSERT INTO `eb_migrations` VALUES (548,'2024_11_07_144251_create_client_list_table',0);
INSERT INTO `eb_migrations` VALUES (549,'2024_11_07_144251_create_client_remind_table',0);
INSERT INTO `eb_migrations` VALUES (550,'2024_11_07_144251_create_client_shift_table',0);
INSERT INTO `eb_migrations` VALUES (551,'2024_11_07_144251_create_client_subscribe_table',0);
INSERT INTO `eb_migrations` VALUES (553,'2024_11_07_144251_create_contract_table',0);
INSERT INTO `eb_migrations` VALUES (554,'2024_11_07_144251_create_contract_resource_table',0);
INSERT INTO `eb_migrations` VALUES (555,'2024_11_07_144251_create_customer_table',0);
INSERT INTO `eb_migrations` VALUES (556,'2024_11_07_144251_create_customer_liaison_table',0);
INSERT INTO `eb_migrations` VALUES (557,'2024_11_07_144251_create_customer_record_table',0);
INSERT INTO `eb_migrations` VALUES (558,'2024_11_07_144251_create_daily_report_member_table',0);
INSERT INTO `eb_migrations` VALUES (559,'2024_11_07_144251_create_dict_data_table',0);
INSERT INTO `eb_migrations` VALUES (560,'2024_11_07_144251_create_dict_type_table',0);
INSERT INTO `eb_migrations` VALUES (561,'2024_11_07_144251_create_dingdan_table',0);
INSERT INTO `eb_migrations` VALUES (562,'2024_11_07_144251_create_employee_train_table',0);
INSERT INTO `eb_migrations` VALUES (563,'2024_11_07_144251_create_enterprise_table',0);
INSERT INTO `eb_migrations` VALUES (564,'2024_11_07_144251_create_enterprise_config_table',0);
INSERT INTO `eb_migrations` VALUES (565,'2024_11_07_144251_create_enterprise_file_table',0);
INSERT INTO `eb_migrations` VALUES (566,'2024_11_07_144251_create_enterprise_file_change_table',0);
INSERT INTO `eb_migrations` VALUES (567,'2024_11_07_144251_create_enterprise_file_folder_table',0);
INSERT INTO `eb_migrations` VALUES (568,'2024_11_07_144251_create_enterprise_file_permissions_table',0);
INSERT INTO `eb_migrations` VALUES (569,'2024_11_07_144251_create_enterprise_log_0_table',0);
INSERT INTO `eb_migrations` VALUES (570,'2024_11_07_144251_create_enterprise_log_1_table',0);
INSERT INTO `eb_migrations` VALUES (571,'2024_11_07_144251_create_enterprise_menus_table',0);
INSERT INTO `eb_migrations` VALUES (572,'2024_11_07_144251_create_enterprise_message_notice_table',0);
INSERT INTO `eb_migrations` VALUES (573,'2024_11_07_144251_create_enterprise_notice_table',0);
INSERT INTO `eb_migrations` VALUES (574,'2024_11_07_144251_create_enterprise_notice_visit_table',0);
INSERT INTO `eb_migrations` VALUES (575,'2024_11_07_144251_create_enterprise_paytype_table',0);
INSERT INTO `eb_migrations` VALUES (576,'2024_11_07_144251_create_enterprise_role_table',0);
INSERT INTO `eb_migrations` VALUES (577,'2024_11_07_144251_create_enterprise_role_user_table',0);
INSERT INTO `eb_migrations` VALUES (578,'2024_11_07_144251_create_enterprise_target_table',0);
INSERT INTO `eb_migrations` VALUES (579,'2024_11_07_144251_create_enterprise_target_category_table',0);
INSERT INTO `eb_migrations` VALUES (580,'2024_11_07_144251_create_enterprise_template_table',0);
INSERT INTO `eb_migrations` VALUES (581,'2024_11_07_144251_create_enterprise_template_collect_table',0);
INSERT INTO `eb_migrations` VALUES (582,'2024_11_07_144251_create_enterprise_user_change_table',0);
INSERT INTO `eb_migrations` VALUES (583,'2024_11_07_144251_create_enterprise_user_daily_table',0);
INSERT INTO `eb_migrations` VALUES (584,'2024_11_07_144251_create_enterprise_user_daily_reply_table',0);
INSERT INTO `eb_migrations` VALUES (585,'2024_11_07_144251_create_enterprise_user_education_table',0);
INSERT INTO `eb_migrations` VALUES (586,'2024_11_07_144251_create_enterprise_user_job_analysis_table',0);
INSERT INTO `eb_migrations` VALUES (587,'2024_11_07_144251_create_enterprise_user_position_table',0);
INSERT INTO `eb_migrations` VALUES (588,'2024_11_07_144251_create_enterprise_user_role_table',0);
INSERT INTO `eb_migrations` VALUES (589,'2024_11_07_144251_create_enterprise_user_salary_table',0);
INSERT INTO `eb_migrations` VALUES (590,'2024_11_07_144251_create_enterprise_user_scope_table',0);
INSERT INTO `eb_migrations` VALUES (591,'2024_11_07_144251_create_enterprise_user_work_table',0);
INSERT INTO `eb_migrations` VALUES (592,'2024_11_07_144251_create_faguohou_table',0);
INSERT INTO `eb_migrations` VALUES (593,'2024_11_07_144251_create_failed_jobs_table',0);
INSERT INTO `eb_migrations` VALUES (594,'2024_11_07_144251_create_folder_table',0);
INSERT INTO `eb_migrations` VALUES (595,'2024_11_07_144251_create_folder_auth_table',0);
INSERT INTO `eb_migrations` VALUES (596,'2024_11_07_144251_create_folder_collaborate_table',0);
INSERT INTO `eb_migrations` VALUES (597,'2024_11_07_144251_create_folder_history_table',0);
INSERT INTO `eb_migrations` VALUES (598,'2024_11_07_144251_create_folder_share_table',0);
INSERT INTO `eb_migrations` VALUES (599,'2024_11_07_144251_create_folder_view_history_table',0);
INSERT INTO `eb_migrations` VALUES (600,'2024_11_07_144251_create_form_cate_table',0);
INSERT INTO `eb_migrations` VALUES (601,'2024_11_07_144251_create_form_data_table',0);
INSERT INTO `eb_migrations` VALUES (602,'2024_11_07_144251_create_frame_table',0);
INSERT INTO `eb_migrations` VALUES (603,'2024_11_07_144251_create_frame_assist_table',0);
INSERT INTO `eb_migrations` VALUES (604,'2024_11_07_144251_create_gongheguoha_table',0);
INSERT INTO `eb_migrations` VALUES (605,'2024_11_07_144251_create_gongzitiaojiegou_table',0);
INSERT INTO `eb_migrations` VALUES (606,'2024_11_07_144251_create_gongzitiaojilu_table',0);
INSERT INTO `eb_migrations` VALUES (607,'2024_11_07_144251_create_hay_group_table',0);
INSERT INTO `eb_migrations` VALUES (608,'2024_11_07_144251_create_hay_group_data_table',0);
INSERT INTO `eb_migrations` VALUES (612,'2024_11_07_144251_create_kehuguanli_table',0);
INSERT INTO `eb_migrations` VALUES (614,'2024_11_07_144251_create_message_table',0);
INSERT INTO `eb_migrations` VALUES (615,'2024_11_07_144251_create_message_category_table',0);
INSERT INTO `eb_migrations` VALUES (616,'2024_11_07_144251_create_message_subscribe_table',0);
INSERT INTO `eb_migrations` VALUES (617,'2024_11_07_144251_create_message_template_table',0);
INSERT INTO `eb_migrations` VALUES (618,'2024_11_07_144251_create_openapi_key_table',0);
INSERT INTO `eb_migrations` VALUES (619,'2024_11_07_144251_create_openapi_rule_table',0);
INSERT INTO `eb_migrations` VALUES (620,'2024_11_07_144251_create_openapi_rule_copy1_table',0);
INSERT INTO `eb_migrations` VALUES (621,'2024_11_07_144251_create_openapi_rule_copy2_table',0);
INSERT INTO `eb_migrations` VALUES (624,'2024_11_07_144251_create_program_table',0);
INSERT INTO `eb_migrations` VALUES (625,'2024_11_07_144251_create_program_dynamic_table',0);
INSERT INTO `eb_migrations` VALUES (626,'2024_11_07_144251_create_program_member_table',0);
INSERT INTO `eb_migrations` VALUES (627,'2024_11_07_144251_create_program_task_table',0);
INSERT INTO `eb_migrations` VALUES (628,'2024_11_07_144251_create_program_task_comment_table',0);
INSERT INTO `eb_migrations` VALUES (629,'2024_11_07_144251_create_program_task_member_table',0);
INSERT INTO `eb_migrations` VALUES (630,'2024_11_07_144251_create_program_version_table',0);
INSERT INTO `eb_migrations` VALUES (631,'2024_11_07_144251_create_promotion_table',0);
INSERT INTO `eb_migrations` VALUES (632,'2024_11_07_144251_create_promotion_data_table',0);
INSERT INTO `eb_migrations` VALUES (633,'2024_11_07_144251_create_rank_table',0);
INSERT INTO `eb_migrations` VALUES (634,'2024_11_07_144251_create_rank_category_table',0);
INSERT INTO `eb_migrations` VALUES (635,'2024_11_07_144251_create_rank_job_table',0);
INSERT INTO `eb_migrations` VALUES (636,'2024_11_07_144251_create_rank_level_table',0);
INSERT INTO `eb_migrations` VALUES (637,'2024_11_07_144251_create_rank_relation_table',0);
INSERT INTO `eb_migrations` VALUES (638,'2024_11_07_144251_create_roster_cycle_table',0);
INSERT INTO `eb_migrations` VALUES (639,'2024_11_07_144251_create_roster_cycle_shift_table',0);
INSERT INTO `eb_migrations` VALUES (640,'2024_11_07_144251_create_rules_table',0);
INSERT INTO `eb_migrations` VALUES (641,'2024_11_07_144251_create_salesman_custom_field_table',0);
INSERT INTO `eb_migrations` VALUES (642,'2024_11_07_144251_create_schedule_table',0);
INSERT INTO `eb_migrations` VALUES (643,'2024_11_07_144251_create_schedule_record_table',0);
INSERT INTO `eb_migrations` VALUES (644,'2024_11_07_144251_create_schedule_remind_table',0);
INSERT INTO `eb_migrations` VALUES (645,'2024_11_07_144251_create_schedule_reply_table',0);
INSERT INTO `eb_migrations` VALUES (646,'2024_11_07_144251_create_schedule_task_table',0);
INSERT INTO `eb_migrations` VALUES (647,'2024_11_07_144251_create_schedule_type_table',0);
INSERT INTO `eb_migrations` VALUES (648,'2024_11_07_144251_create_schedule_user_table',0);
INSERT INTO `eb_migrations` VALUES (649,'2024_11_07_144251_create_storage_table',0);
INSERT INTO `eb_migrations` VALUES (650,'2024_11_07_144251_create_storage_category_table',0);
INSERT INTO `eb_migrations` VALUES (651,'2024_11_07_144251_create_storage_record_table',0);
INSERT INTO `eb_migrations` VALUES (652,'2024_11_07_144251_create_sub_table_table',0);
INSERT INTO `eb_migrations` VALUES (653,'2024_11_07_144251_create_system_admin_table',0);
INSERT INTO `eb_migrations` VALUES (654,'2024_11_07_144251_create_system_attach_table',0);
INSERT INTO `eb_migrations` VALUES (655,'2024_11_07_144251_create_system_backup_table',0);
INSERT INTO `eb_migrations` VALUES (656,'2024_11_07_144251_create_system_city_table',0);
INSERT INTO `eb_migrations` VALUES (657,'2024_11_07_144251_create_system_config_table',0);
INSERT INTO `eb_migrations` VALUES (658,'2024_11_07_144251_create_system_crud_table',0);
INSERT INTO `eb_migrations` VALUES (659,'2024_11_07_144251_create_system_crud_approve_table',0);
INSERT INTO `eb_migrations` VALUES (660,'2024_11_07_144251_create_system_crud_approve_process_table',0);
INSERT INTO `eb_migrations` VALUES (661,'2024_11_07_144251_create_system_crud_approve_rule_table',0);
INSERT INTO `eb_migrations` VALUES (662,'2024_11_07_144251_create_system_crud_cate_table',0);
INSERT INTO `eb_migrations` VALUES (663,'2024_11_07_144251_create_system_crud_curl_table',0);
INSERT INTO `eb_migrations` VALUES (664,'2024_11_07_144251_create_system_crud_dashboard_table',0);
INSERT INTO `eb_migrations` VALUES (665,'2024_11_07_144251_create_system_crud_event_table',0);
INSERT INTO `eb_migrations` VALUES (666,'2024_11_07_144251_create_system_crud_event_log_table',0);
INSERT INTO `eb_migrations` VALUES (667,'2024_11_07_144251_create_system_crud_field_table',0);
INSERT INTO `eb_migrations` VALUES (668,'2024_11_07_144251_create_system_crud_form_table',0);
INSERT INTO `eb_migrations` VALUES (669,'2024_11_07_144251_create_system_crud_role_table',0);
INSERT INTO `eb_migrations` VALUES (670,'2024_11_07_144251_create_system_crud_senior_search_table',0);
INSERT INTO `eb_migrations` VALUES (671,'2024_11_07_144251_create_system_crud_table_table',0);
INSERT INTO `eb_migrations` VALUES (672,'2024_11_07_144251_create_system_crud_table_user_table',0);
INSERT INTO `eb_migrations` VALUES (673,'2024_11_07_144251_create_system_group_table',0);
INSERT INTO `eb_migrations` VALUES (674,'2024_11_07_144251_create_system_group_data_table',0);
INSERT INTO `eb_migrations` VALUES (675,'2024_11_07_144251_create_system_menus_table',0);
INSERT INTO `eb_migrations` VALUES (676,'2024_11_07_144251_create_system_package_table',0);
INSERT INTO `eb_migrations` VALUES (677,'2024_11_07_144251_create_system_paytype_table',0);
INSERT INTO `eb_migrations` VALUES (678,'2024_11_07_144251_create_system_quick_table',0);
INSERT INTO `eb_migrations` VALUES (679,'2024_11_07_144251_create_system_role_table',0);
INSERT INTO `eb_migrations` VALUES (680,'2024_11_07_144251_create_system_storage_table',0);
INSERT INTO `eb_migrations` VALUES (681,'2024_11_07_144251_create_task_table',0);
INSERT INTO `eb_migrations` VALUES (682,'2024_11_07_144251_create_task_run_record_table',0);
INSERT INTO `eb_migrations` VALUES (683,'2024_11_07_144251_create_user_assess_table',0);
INSERT INTO `eb_migrations` VALUES (684,'2024_11_07_144251_create_user_card_perfect_table',0);
INSERT INTO `eb_migrations` VALUES (685,'2024_11_07_144251_create_user_change_table',0);
INSERT INTO `eb_migrations` VALUES (686,'2024_11_07_144251_create_user_education_history_table',0);
INSERT INTO `eb_migrations` VALUES (687,'2024_11_07_144251_create_user_enterprise_apply_table',0);
INSERT INTO `eb_migrations` VALUES (688,'2024_11_07_144251_create_user_enterprise_invite_table',0);
INSERT INTO `eb_migrations` VALUES (689,'2024_11_07_144251_create_user_memorial_table',0);
INSERT INTO `eb_migrations` VALUES (690,'2024_11_07_144251_create_user_memorial_category_table',0);
INSERT INTO `eb_migrations` VALUES (691,'2024_11_07_144251_create_user_pending_table',0);
INSERT INTO `eb_migrations` VALUES (692,'2024_11_07_144251_create_user_quick_table',0);
INSERT INTO `eb_migrations` VALUES (693,'2024_11_07_144251_create_user_remind_log_table',0);
INSERT INTO `eb_migrations` VALUES (694,'2024_11_07_144251_create_user_resume_table',0);
INSERT INTO `eb_migrations` VALUES (695,'2024_11_07_144251_create_user_schedule_table',0);
INSERT INTO `eb_migrations` VALUES (696,'2024_11_07_144251_create_user_schedule_record_table',0);
INSERT INTO `eb_migrations` VALUES (697,'2024_11_07_144251_create_user_token_table',0);
INSERT INTO `eb_migrations` VALUES (698,'2024_11_07_144251_create_user_work_history_table',0);
INSERT INTO `eb_migrations` VALUES (700,'2024_11_26_114028_update_dict_data_table',17);
INSERT INTO `eb_migrations` VALUES (701,'2024_11_29_143650_update_system_crud',18);
INSERT INTO `eb_migrations` VALUES (702,'2024_11_29_153220_system_crud_log',18);
INSERT INTO `eb_migrations` VALUES (703,'2024_11_29_153227_system_crud_comment',18);
INSERT INTO `eb_migrations` VALUES (704,'2024_12_06_151137_system_crud_data_share',19);
INSERT INTO `eb_migrations` VALUES (705,'2024_12_06_151242_system_crud_share',19);
INSERT INTO `eb_migrations` VALUES (706,'2024_12_07_093358_system_crud_questionnaire',19);
INSERT INTO `eb_migrations` VALUES (707,'2024_12_10_091837_update_system_crud_role_table',20);
INSERT INTO `eb_migrations` VALUES (708,'2024_12_11_100607_update_system_crud_event_table',21);
INSERT INTO `eb_migrations` VALUES (709,'2025_01_09_112044_update_message_template_table',22);
INSERT INTO `eb_migrations` VALUES (710,'2025_01_09_112084_update_message_table',23);
INSERT INTO `eb_migrations` VALUES (711,'2025_01_13_182033_create_system_crud_approve_record_table',24);
INSERT INTO `eb_migrations` VALUES (712,'2025_02_27_172846_chat_models',25);
INSERT INTO `eb_migrations` VALUES (713,'2025_02_27_173418_chat_applications',26);
INSERT INTO `eb_migrations` VALUES (715,'2025_02_28_162334_create_chat_history',27);
INSERT INTO `eb_migrations` VALUES (716,'2025_03_04_115818_chat_app_auth',28);
INSERT INTO `eb_migrations` VALUES (717,'2025_02_28_161758_create_chat_record',29);

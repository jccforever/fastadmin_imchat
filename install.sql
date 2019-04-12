
CREATE TABLE IF NOT EXISTS `__PREFIX__imchat_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL DEFAULT 0 COMMENT '房间',
  `from_uid` int(11) NOT NULL DEFAULT 0 COMMENT '消息来源',
  `to_uid` int(11) NOT NULL DEFAULT 0 COMMENT '消息接收源',
  `content` text COMMENT '消息内容',
  `status` enum('0','1') DEFAULT '0' COMMENT '状态:0=未阅读,1=已阅读',
  `createtime` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  index (room_id),
  index (from_uid),
  index (to_uid)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='消息记录表';

CREATE TABLE IF NOT EXISTS `__PREFIX__imchat_group_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL DEFAULT 0 COMMENT '房间id',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户id',
  `last_message_id` int(11) NOT NULL DEFAULT 0 COMMENT '最后已读id',
  PRIMARY KEY (`id`),
  index (room_id),
  index (user_id),
  index (last_message_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='群组消息阅读表';

CREATE TABLE IF NOT EXISTS `__PREFIX__imchat_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL COMMENT '房间名称',
  `intro` varchar(150) DEFAULT '' COMMENT '房间简介',
  `img` varchar(100) DEFAULT '' COMMENT '房间头像',
  `createtime` int(11) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(11) DEFAULT NULL COMMENT '最后更改时间',
  `status` enum('0','1') DEFAULT '1' COMMENT '状态:0=隐藏,1=正常',
  PRIMARY KEY (`id`)
)  ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8 COMMENT='聊天室房间表';
BEGIN;
insert into `__PREFIX__imchat_room` values(1,'全体人员群组', '包含本系统所有人员', '',0,0,'1');
COMMIT;

CREATE TABLE IF NOT EXISTS `__PREFIX__imchat_room_user` (
  `room_id` int(11) NOT NULL DEFAULT '0' COMMENT '房间id',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `createtime` int(11) NOT NULL DEFAULT '0' COMMENT '加入时间',
  PRIMARY KEY (`room_id`, `uid`),
  index (room_id),
  index (uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='房间用户关系表';

CREATE TABLE IF NOT EXISTS `__PREFIX__imchat_cert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cert` varchar(170) NOT NULL  COMMENT '连接凭证',
  `expire_time` int(11) NOT NULL  COMMENT '过期时间',
  PRIMARY KEY (`id`),
  index (cert)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='连接凭证表';


ALTER TABLE `yoshop_coupon`
ADD COLUMN `apply_range_config`  text NOT NULL COMMENT '适用范围配置(json格式)' AFTER `apply_range`;


ALTER TABLE `yoshop_user_coupon`
ADD COLUMN `apply_range_config`  text NOT NULL COMMENT '适用范围配置(json格式)' AFTER `apply_range`;


ALTER TABLE `yoshop_coupon`
MODIFY COLUMN `apply_range`  tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '适用范围(10全部商品 20指定商品 30排除商品)' AFTER `end_time`;


ALTER TABLE `yoshop_user_coupon`
MODIFY COLUMN `apply_range`  tinyint(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '适用范围(10全部商品 20指定商品 30排除商品)' AFTER `end_time`;


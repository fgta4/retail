-- SET FOREIGN_KEY_CHECKS=0;

-- drop table if exists `fsn_mercharticle`;
-- drop table if exists `fsn_merchitem`;


CREATE TABLE IF NOT EXISTS `fsn_mercharticle` (
	`mercharticle_id` varchar(14) NOT NULL , 
	`mercharticle_art` varchar(30)  , 
	`mercharticle_mat` varchar(30)  , 
	`mercharticle_col` varchar(30)  , 
	`mercharticle_name` varchar(255)  , 
	`mercharticle_descr` varchar(2500)  , 
	`mercharticle_isdisabled` tinyint(1) NOT NULL DEFAULT 0, 
	`mercharticle_pcpline` varchar(90)  , 
	`mercharticle_pcpgroup` varchar(90)  , 
	`mercharticle_pcpcategory` varchar(90)  , 
	`mercharticle_gender` varchar(1)  , 
	`mercharticle_fit` varchar(30)  , 
	`mercharticle_hscodeship` varchar(30)  , 
	`mercharticle_hscodeina` varchar(30)  , 
	`mercharticle_gtype` varchar(5)  , 
	`mercharticle_labelname` varchar(90)  , 
	`mercharticle_labelproduct` varchar(90)  , 
	`mercharticle_bahan` varchar(150)  , 
	`mercharticle_pemeliharaan` varchar(150)  , 
	`mercharticle_logo` varchar(30)  , 
	`mercharticle_dibuatdi` varchar(30)  , 
	`merchctg_id` varchar(30) NOT NULL , 
	`merchsea_id` varchar(10) NOT NULL , 
	`unit_id` varchar(10) NOT NULL , 
	`brand_id` varchar(14) NOT NULL , 
	`dept_id` varchar(30) NOT NULL , 
	`_createby` varchar(14) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(14)  , 
	`_modifydate` datetime  , 
	UNIQUE KEY `mercharticle_uniq` (`dept_id`, `mercharticle_art`, `mercharticle_mat`, `mercharticle_col`),
	PRIMARY KEY (`mercharticle_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar article merchandise';


ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_art` varchar(30)   AFTER `mercharticle_id`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_mat` varchar(30)   AFTER `mercharticle_art`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_col` varchar(30)   AFTER `mercharticle_mat`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_name` varchar(255)   AFTER `mercharticle_col`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_descr` varchar(2500)   AFTER `mercharticle_name`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_isdisabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `mercharticle_descr`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_pcpline` varchar(90)   AFTER `mercharticle_isdisabled`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_pcpgroup` varchar(90)   AFTER `mercharticle_pcpline`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_pcpcategory` varchar(90)   AFTER `mercharticle_pcpgroup`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_gender` varchar(1)   AFTER `mercharticle_pcpcategory`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_fit` varchar(30)   AFTER `mercharticle_gender`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_hscodeship` varchar(30)   AFTER `mercharticle_fit`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_hscodeina` varchar(30)   AFTER `mercharticle_hscodeship`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_gtype` varchar(5)   AFTER `mercharticle_hscodeina`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_labelname` varchar(90)   AFTER `mercharticle_gtype`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_labelproduct` varchar(90)   AFTER `mercharticle_labelname`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_bahan` varchar(150)   AFTER `mercharticle_labelproduct`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_pemeliharaan` varchar(150)   AFTER `mercharticle_bahan`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_logo` varchar(30)   AFTER `mercharticle_pemeliharaan`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `mercharticle_dibuatdi` varchar(30)   AFTER `mercharticle_logo`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `merchctg_id` varchar(30) NOT NULL  AFTER `mercharticle_dibuatdi`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `merchsea_id` varchar(10) NOT NULL  AFTER `merchctg_id`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `unit_id` varchar(10) NOT NULL  AFTER `merchsea_id`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `brand_id` varchar(14) NOT NULL  AFTER `unit_id`;
ALTER TABLE `fsn_mercharticle` ADD COLUMN IF NOT EXISTS  `dept_id` varchar(30) NOT NULL  AFTER `brand_id`;


ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_art` varchar(30)    AFTER `mercharticle_id`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_mat` varchar(30)    AFTER `mercharticle_art`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_col` varchar(30)    AFTER `mercharticle_mat`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_name` varchar(255)    AFTER `mercharticle_col`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_descr` varchar(2500)    AFTER `mercharticle_name`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_isdisabled` tinyint(1) NOT NULL DEFAULT 0  AFTER `mercharticle_descr`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_pcpline` varchar(90)    AFTER `mercharticle_isdisabled`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_pcpgroup` varchar(90)    AFTER `mercharticle_pcpline`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_pcpcategory` varchar(90)    AFTER `mercharticle_pcpgroup`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_gender` varchar(1)    AFTER `mercharticle_pcpcategory`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_fit` varchar(30)    AFTER `mercharticle_gender`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_hscodeship` varchar(30)    AFTER `mercharticle_fit`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_hscodeina` varchar(30)    AFTER `mercharticle_hscodeship`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_gtype` varchar(5)    AFTER `mercharticle_hscodeina`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_labelname` varchar(90)    AFTER `mercharticle_gtype`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_labelproduct` varchar(90)    AFTER `mercharticle_labelname`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_bahan` varchar(150)    AFTER `mercharticle_labelproduct`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_pemeliharaan` varchar(150)    AFTER `mercharticle_bahan`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_logo` varchar(30)    AFTER `mercharticle_pemeliharaan`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `mercharticle_dibuatdi` varchar(30)    AFTER `mercharticle_logo`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `merchctg_id` varchar(30) NOT NULL   AFTER `mercharticle_dibuatdi`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `merchsea_id` varchar(10) NOT NULL   AFTER `merchctg_id`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `unit_id` varchar(10) NOT NULL   AFTER `merchsea_id`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `brand_id` varchar(14) NOT NULL   AFTER `unit_id`;
ALTER TABLE `fsn_mercharticle` MODIFY COLUMN IF EXISTS  `dept_id` varchar(30) NOT NULL   AFTER `brand_id`;


ALTER TABLE `fsn_mercharticle` ADD CONSTRAINT `mercharticle_uniq` UNIQUE IF NOT EXISTS  (`dept_id`, `mercharticle_art`, `mercharticle_mat`, `mercharticle_col`);

ALTER TABLE `fsn_mercharticle` ADD KEY IF NOT EXISTS  `merchctg_id` (`merchctg_id`);
ALTER TABLE `fsn_mercharticle` ADD KEY IF NOT EXISTS  `merchsea_id` (`merchsea_id`);
ALTER TABLE `fsn_mercharticle` ADD KEY IF NOT EXISTS  `unit_id` (`unit_id`);
ALTER TABLE `fsn_mercharticle` ADD KEY IF NOT EXISTS  `brand_id` (`brand_id`);
ALTER TABLE `fsn_mercharticle` ADD KEY IF NOT EXISTS  `dept_id` (`dept_id`);

ALTER TABLE `fsn_mercharticle` ADD CONSTRAINT `fk_fsn_mercharticle_fsn_merchctg` FOREIGN KEY IF NOT EXISTS (`merchctg_id`) REFERENCES `fsn_merchctg` (`merchctg_id`);
ALTER TABLE `fsn_mercharticle` ADD CONSTRAINT `fk_fsn_mercharticle_fsn_merchsea` FOREIGN KEY IF NOT EXISTS (`merchsea_id`) REFERENCES `fsn_merchsea` (`merchsea_id`);
ALTER TABLE `fsn_mercharticle` ADD CONSTRAINT `fk_fsn_mercharticle_mst_unit` FOREIGN KEY IF NOT EXISTS (`unit_id`) REFERENCES `mst_unit` (`unit_id`);
ALTER TABLE `fsn_mercharticle` ADD CONSTRAINT `fk_fsn_mercharticle_mst_brand` FOREIGN KEY IF NOT EXISTS (`brand_id`) REFERENCES `mst_brand` (`brand_id`);
ALTER TABLE `fsn_mercharticle` ADD CONSTRAINT `fk_fsn_mercharticle_mst_dept` FOREIGN KEY IF NOT EXISTS (`dept_id`) REFERENCES `mst_dept` (`dept_id`);





CREATE TABLE IF NOT EXISTS `fsn_merchitem` (
	`merchitem_id` varchar(14) NOT NULL , 
	`merchitem_size` varchar(20)  , 
	`merchitem_isdisabled` tinyint(1) NOT NULL DEFAULT 0, 
	`unit_id` varchar(10) NOT NULL , 
	`brand_id` varchar(14) NOT NULL , 
	`dept_id` varchar(30) NOT NULL , 
	`mercharticle_id` varchar(14) NOT NULL , 
	`_createby` varchar(14) NOT NULL , 
	`_createdate` datetime NOT NULL DEFAULT current_timestamp(), 
	`_modifyby` varchar(14)  , 
	`_modifydate` datetime  , 
	UNIQUE KEY `merchitem_uniq` (`mercharticle_id`, `merchitem_size`),
	PRIMARY KEY (`merchitem_id`)
) 
ENGINE=InnoDB
COMMENT='Daftar Item merchandise';


ALTER TABLE `fsn_merchitem` ADD COLUMN IF NOT EXISTS  `merchitem_size` varchar(20)   AFTER `merchitem_id`;
ALTER TABLE `fsn_merchitem` ADD COLUMN IF NOT EXISTS  `merchitem_isdisabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `merchitem_size`;
ALTER TABLE `fsn_merchitem` ADD COLUMN IF NOT EXISTS  `unit_id` varchar(10) NOT NULL  AFTER `merchitem_isdisabled`;
ALTER TABLE `fsn_merchitem` ADD COLUMN IF NOT EXISTS  `brand_id` varchar(14) NOT NULL  AFTER `unit_id`;
ALTER TABLE `fsn_merchitem` ADD COLUMN IF NOT EXISTS  `dept_id` varchar(30) NOT NULL  AFTER `brand_id`;
ALTER TABLE `fsn_merchitem` ADD COLUMN IF NOT EXISTS  `mercharticle_id` varchar(14) NOT NULL  AFTER `dept_id`;


ALTER TABLE `fsn_merchitem` MODIFY COLUMN IF EXISTS  `merchitem_size` varchar(20)    AFTER `merchitem_id`;
ALTER TABLE `fsn_merchitem` MODIFY COLUMN IF EXISTS  `merchitem_isdisabled` tinyint(1) NOT NULL DEFAULT 0  AFTER `merchitem_size`;
ALTER TABLE `fsn_merchitem` MODIFY COLUMN IF EXISTS  `unit_id` varchar(10) NOT NULL   AFTER `merchitem_isdisabled`;
ALTER TABLE `fsn_merchitem` MODIFY COLUMN IF EXISTS  `brand_id` varchar(14) NOT NULL   AFTER `unit_id`;
ALTER TABLE `fsn_merchitem` MODIFY COLUMN IF EXISTS  `dept_id` varchar(30) NOT NULL   AFTER `brand_id`;
ALTER TABLE `fsn_merchitem` MODIFY COLUMN IF EXISTS  `mercharticle_id` varchar(14) NOT NULL   AFTER `dept_id`;


ALTER TABLE `fsn_merchitem` ADD CONSTRAINT `merchitem_uniq` UNIQUE IF NOT EXISTS  (`mercharticle_id`, `merchitem_size`);

ALTER TABLE `fsn_merchitem` ADD KEY IF NOT EXISTS  `merchitem_id` (`merchitem_id`);
ALTER TABLE `fsn_merchitem` ADD KEY IF NOT EXISTS  `unit_id` (`unit_id`);
ALTER TABLE `fsn_merchitem` ADD KEY IF NOT EXISTS  `brand_id` (`brand_id`);
ALTER TABLE `fsn_merchitem` ADD KEY IF NOT EXISTS  `dept_id` (`dept_id`);
ALTER TABLE `fsn_merchitem` ADD KEY IF NOT EXISTS `mercharticle_id` (`mercharticle_id`);

ALTER TABLE `fsn_merchitem` ADD CONSTRAINT `fk_fsn_merchitem_mst_itemstock` FOREIGN KEY IF NOT EXISTS (`merchitem_id`) REFERENCES `mst_itemstock` (`itemstock_id`);
ALTER TABLE `fsn_merchitem` ADD CONSTRAINT `fk_fsn_merchitem_mst_unit` FOREIGN KEY IF NOT EXISTS (`unit_id`) REFERENCES `mst_unit` (`unit_id`);
ALTER TABLE `fsn_merchitem` ADD CONSTRAINT `fk_fsn_merchitem_mst_brand` FOREIGN KEY IF NOT EXISTS (`brand_id`) REFERENCES `mst_brand` (`brand_id`);
ALTER TABLE `fsn_merchitem` ADD CONSTRAINT `fk_fsn_merchitem_mst_dept` FOREIGN KEY IF NOT EXISTS (`dept_id`) REFERENCES `mst_dept` (`dept_id`);
ALTER TABLE `fsn_merchitem` ADD CONSTRAINT `fk_fsn_merchitem_fsn_mercharticle` FOREIGN KEY IF NOT EXISTS (`mercharticle_id`) REFERENCES `fsn_mercharticle` (`mercharticle_id`);






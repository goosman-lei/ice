CREATE DATABASE IF NOT EXISTS ice_home 
    DEFAULT CHARACTER SET UTF8MB4;

USE ice_home;

DROP TABLE IF EXISTS navigation;
CREATE TABLE navigation (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    pid INT NOT NULL DEFAULT 0 COMMENT '父节点id',
    name VARCHAR(128) NOT NULL DEFAULT '' COMMENT '导航名',
    url VARCHAR(256) NOT NULL DEFAULT '' COMMENT '导航地址',
    INDEX idx_pid(pid)
) ENGINE = Innodb CHARACTER SET  UTF8MB4;

INSERT INTO navigation VALUES
    (1, 0, '文档', '/manual/index'),
        (2, 1, '介绍', ''),
            (3, 2, '整体架构', '/manual/markdown/architecture'),
            (4, 2, '文件结构', '/manual/markdown/file-structure')
;
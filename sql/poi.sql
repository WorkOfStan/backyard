--
-- Table structure for table `poi_category`
--

CREATE TABLE IF NOT EXISTS `poi_category` (
    `id` int(11) NOT NULL,
    `name` varchar(100) COLLATE utf8_czech_ci NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci COMMENT = 'Naming of poi catgories for table poi_list';


--
-- Table structure for table `poi_list`
--

CREATE TABLE IF NOT EXISTS `poi_list` (
    `poi_id` int(11) NOT NULL AUTO_INCREMENT,
    `category` int(11) NOT NULL,
    `mesto` varchar(100) COLLATE utf8_czech_ci NOT NULL,
    `PSC` char(6) COLLATE utf8_czech_ci NOT NULL,
    `adresa` varchar(200) COLLATE utf8_czech_ci NOT NULL,
    `long` double NOT NULL COMMENT 'X',
    `lat` double NOT NULL COMMENT 'Y',
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`poi_id`),
    KEY `lat` (`lat`),
    KEY `long` (`long`),
    KEY `category` (`category`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci AUTO_INCREMENT = 88;


-- Display both by
-- SELECT * FROM `poi_list` JOIN `poi_category` 
-- ON `poi_list`.`category` = `poi_category`.id

-- MS Daily Dashboard - Database Schema
-- Auto-created by bin/db-setup.php

CREATE DATABASE IF NOT EXISTS `login`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE `login`;

-- Session table
CREATE TABLE IF NOT EXISTS `session` (
    `ID`       INT(11)    NOT NULL AUTO_INCREMENT,
    `UserName` MEDIUMTEXT NOT NULL,
    `Session`  MEDIUMTEXT NOT NULL,
    `Expired`  DATETIME   NOT NULL,
    PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- User table
CREATE TABLE IF NOT EXISTS `user` (
    `ID`       INT(11)    NOT NULL AUTO_INCREMENT,
    `UserName` MEDIUMTEXT NOT NULL,
    `Password` MEDIUMTEXT NOT NULL,
    PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

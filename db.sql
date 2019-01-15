SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `dbLOI` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `dbLOI`;

CREATE TABLE `leden` (
  `ID` int(11) NOT NULL,
  `Voornaam` varchar(20) NOT NULL,
  `Tussenvoegsels` varchar(15) DEFAULT NULL,
  `Achternaam` varchar(30) NOT NULL,
  `Straat` varchar(50) DEFAULT NULL,
  `Huisnummer` varchar(10) DEFAULT NULL,
  `Postcode` varchar(6) DEFAULT NULL,
  `Woonplaats` varchar(30) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `Geboortedatum` date DEFAULT NULL,
  `Geslacht` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `lidmaatschap` (
  `ID` int(11) NOT NULL,
  `LedenID` int(11) NOT NULL,
  `Datumingang` date NOT NULL,
  `Datumeinde` date DEFAULT NULL,
  `Sportonderdeel` varchar(30) NOT NULL,
  `Lesdag` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `leden`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID` (`ID`);

ALTER TABLE `lidmaatschap`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `LedenID` (`LedenID`);


ALTER TABLE `leden`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lidmaatschap`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `lidmaatschap`
  ADD CONSTRAINT `lidmaatschap_ibfk_1` FOREIGN KEY (`LedenID`) REFERENCES `leden` (`ID`);

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `devices` (
  `deviceid` int(11) NOT NULL,
  `device` text NOT NULL,
  `ipaddress` text NOT NULL,
  `type` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `ports` (
  `id` int(11) NOT NULL,
  `devicename` varchar(50) NOT NULL,
  `interfacename` varchar(30) NOT NULL,
  `interfaceoid` varchar(20) NOT NULL,
  `deviceid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `statistics` (
  `id` int(11) NOT NULL,
  `erroroid` varchar(100) CHARACTER SET utf8 NOT NULL,
  `interfaceerror` varchar(15) NOT NULL,
  `highspeedoid` varchar(100) NOT NULL,
  `ifhighspeed` varchar(15) NOT NULL,
  `time` timestamp NOT NULL,
  `portid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `devices`
  ADD PRIMARY KEY (`deviceid`);

ALTER TABLE `ports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`interfacename`,`deviceid`),
  ADD KEY `newkey_idx` (`deviceid`);

ALTER TABLE `statistics`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `devices`
  MODIFY `deviceid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2687;

ALTER TABLE `ports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21439;

ALTER TABLE `statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1808;

ALTER TABLE `ports`
  ADD CONSTRAINT `newkey` FOREIGN KEY (`deviceid`) REFERENCES `devices` (`deviceid`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

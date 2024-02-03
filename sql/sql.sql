
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `cms`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `article`
--

CREATE TABLE `article` (
  `art_id` int(11) NOT NULL,
  `art_title` varchar(2047) COLLATE utf8_czech_ci NOT NULL,
  `art_text` text COLLATE utf8_czech_ci NOT NULL,
  `art_date` datetime NOT NULL,
  `art_contributor` varchar(511) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `comment`
--

CREATE TABLE `comment` (
  `com_id` int(11) NOT NULL,
  `com_name` varchar(511) COLLATE utf8_czech_ci NOT NULL,
  `com_email` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `com_text` text COLLATE utf8_czech_ci NOT NULL,
  `com_date` datetime NOT NULL,
  `art_id` int(11) NOT NULL,
  `com_parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`art_id`);

--
-- Indexy pro tabulku `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`com_id`),
  ADD KEY `com_parent_id` (`com_parent_id`),
  ADD KEY `art_id` (`art_id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `article`
--
ALTER TABLE `article`
  MODIFY `art_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `comment`
--
ALTER TABLE `comment`
  MODIFY `com_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `comment_ibfk_1` FOREIGN KEY (`art_id`) REFERENCES `article` (`art_id`),
  ADD CONSTRAINT `comment_ibfk_2` FOREIGN KEY (`com_parent_id`) REFERENCES `comment` (`com_id`);
COMMIT;

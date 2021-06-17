-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 16, 2021 at 08:19 PM
-- Server version: 10.2.38-MariaDB
-- PHP Version: 7.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ab_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `comics`
--

CREATE TABLE `comics` (
  `comic_id` int(11) NOT NULL,
  `comic_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comic_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comic_summary` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comic_creator` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comic_support` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `comic_smedia` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `comic_warning` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `comic_rating` double(3,3) DEFAULT 0.000,
  `rating_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `comic_status` set('Ongoing','Completed','On Hiatus','Cancelled','Deleted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Ongoing',
  `comic_freq` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `comic_crawler` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `comic_pages` longtext COLLATE utf8_unicode_ci NOT NULL,
  `comic_rss` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `comic_mirrors` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_crawl` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `last_edit` int(11) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT NULL,
  `reader_options` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comic_owners`
--

CREATE TABLE `comic_owners` (
  `relation_id` int(11) NOT NULL,
  `comic_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `comic_rating`
--

CREATE TABLE `comic_rating` (
  `rating_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comic_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `comic_tags`
--

CREATE TABLE `comic_tags` (
  `relation_id` int(11) NOT NULL,
  `comic_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `comic_warnings`
--

CREATE TABLE `comic_warnings` (
  `relation_id` int(11) NOT NULL,
  `comic_id` int(11) NOT NULL,
  `warning_id` int(11) NOT NULL,
  `warning_lock` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `locked_comics`
--

CREATE TABLE `locked_comics` (
  `comic_id` int(11) NOT NULL,
  `status` set('locked','banned') COLLATE utf8_unicode_ci NOT NULL,
  `domain` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_ids`
--

CREATE TABLE `oauth_ids` (
  `auth_index` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `oauth_provider` set('Google','Facebook','Twitter') COLLATE utf8_unicode_ci NOT NULL,
  `oauth_uid` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `tag_id` int(11) NOT NULL,
  `tag_name` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `restricted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`tag_id`, `tag_name`, `restricted`) VALUES
(1, '15th Century', 0),
(2, '16th Century', 0),
(3, '17th Century', 0),
(4, '18th Century', 0),
(5, '19th Century', 0),
(6, '3D Render', 0),
(8, 'Action', 0),
(9, 'Adaptation', 0),
(11, 'Adult', 0),
(13, 'Adventure', 0),
(14, 'Afterlife', 0),
(16, 'Aliens', 0),
(21, 'Amnesia', 0),
(23, 'Ancient History', 0),
(24, 'Androids', 0),
(25, 'Angels', 0),
(26, 'Animal Protagonists', 0),
(27, 'Animals', 0),
(30, 'Animesque', 0),
(31, 'Anthology', 0),
(35, 'Arabic Language', 0),
(37, 'Aromantic', 0),
(38, 'Artificial Intelligence', 0),
(39, 'Asexual', 0),
(40, 'Assassins', 0),
(41, 'Astronomy', 0),
(42, 'Author Cameo', 0),
(44, 'Autobiography', 0),
(49, 'Becomics', 0),
(51, 'Bisexual', 0),
(52, 'BL', 0),
(53, 'Black and White', 0),
(55, 'Blog', 0),
(57, 'Body Sharing', 0),
(58, 'Body Swapping', 0),
(59, 'Bounty Hunters', 0),
(61, 'Breaking the Fourth Wall', 0),
(64, 'Cars', 0),
(68, 'Chinese Language', 0),
(70, 'College', 0),
(71, 'Comedy', 0),
(72, 'Comic Fury', 0),
(74, 'Coming of Age', 0),
(75, 'Conspiracy', 0),
(79, 'Crossdressing', 0),
(80, 'Crossover', 0),
(81, 'Crude', 0),
(82, 'Cute', 0),
(83, 'Cyberpunk', 0),
(84, 'Cyborgs', 0),
(86, 'Daily', 0),
(89, 'Dark', 0),
(91, 'Deal with the Devil', 0),
(92, 'Death', 0),
(94, 'Delinquents', 0),
(95, 'Demons', 0),
(96, 'Desert', 0),
(99, 'Dinosaurs', 0),
(102, 'Dragons', 0),
(103, 'Drama', 0),
(106, 'Dystopia', 0),
(108, 'Ecchi', 0),
(109, 'Educational', 0),
(111, 'English Language', 0),
(113, 'Epic', 0),
(114, 'Episodic', 0),
(115, 'Erotic', 0),
(118, 'Experimental', 0),
(125, 'Fantasy', 0),
(128, 'Female Creator', 0),
(129, 'Fetish', 0),
(135, 'Flash Animation', 0),
(136, 'Folktale', 0),
(140, 'French Language', 0),
(141, 'Full Color', 0),
(143, 'Furry', 0),
(148, 'Gay', 0),
(149, 'Geek Culture', 0),
(150, 'Gender Bender', 0),
(151, 'Genderqueer', 0),
(152, 'Genetic Engineering', 0),
(153, 'German Language', 0),
(155, 'Ghosts', 0),
(157, 'Golden Age', 0),
(159, 'Gothic', 0),
(167, 'High School', 0),
(177, 'Ink Drop Cafe', 1),
(297, 'SciFi', 0),
(401, 'Female Lead', 0),
(417, 'In Print', 0),
(418, 'Disabled Character', 0),
(419, 'EIW Winner', 1),
(420, 'EIW Honorable Mention', 1),
(421, 'Eisner Award', 1),
(422, 'Ignatz Award', 1),
(423, 'All Ages', 0),
(427, 'Alternate POV', 0),
(428, 'Alternate Universe', 0),
(429, 'Atompunk', 0),
(430, 'Bittersweet', 0),
(431, 'Commentary', 0),
(432, 'Current Events', 0),
(433, 'D&D Campaign', 0),
(434, 'Detective', 0),
(435, 'Digital Media', 0),
(436, 'Disabilities', 0),
(438, 'Diverse Cast', 0),
(439, 'Edgy', 0),
(440, 'Fairy Tale', 0),
(441, 'Fancomic', 0),
(442, 'Gods', 0),
(443, 'Historical Fiction', 0),
(444, 'Hiveworks', 1),
(445, 'Horror', 0),
(446, 'In Wonderland', 0),
(447, 'Intersex', 0),
(448, 'Japanese Language', 0),
(449, 'Japanese Mythology', 0),
(450, 'Korean Language', 0),
(451, 'Large Archive', 0),
(452, 'Lesbian', 0),
(453, 'Long Form', 0),
(454, 'Lost World', 0),
(455, 'Love Triangle', 0),
(456, 'Magic', 0),
(457, 'Magic School', 0),
(458, 'Magical Girl', 0),
(459, 'Malay Language', 0),
(460, 'Manga', 0),
(461, 'Mech', 0),
(462, 'Medieval', 0),
(463, 'Merchandise', 0),
(464, 'Military', 0),
(465, 'Monsters', 0),
(466, 'MS Paint', 0),
(467, 'Music', 0),
(468, 'Mysterious Shop', 0),
(469, 'Mystery', 0),
(470, 'Mythology', 0),
(471, 'Noir', 0),
(472, 'Nonbinary', 0),
(473, 'Nonfiction', 0),
(474, 'Nuzlocke', 0),
(475, 'Object Head', 0),
(476, 'Occult', 0),
(477, 'Paranormal', 0),
(478, 'Person in a Strange World', 0),
(479, 'Philosophical', 0),
(480, 'Photographic', 0),
(481, 'Pixel Art', 0),
(482, 'Platonic Relationship', 0),
(483, 'POC', 0),
(484, 'POC Creator', 0),
(485, 'Political', 0),
(486, 'Polyamory', 0),
(487, 'Portuguese Language', 0),
(488, 'Post-apocalyptic', 0),
(489, 'Prequel', 0),
(490, 'Proxy Battles', 0),
(491, 'Psychic Powers', 0),
(492, 'Psychological', 0),
(493, 'Pulp', 0),
(494, 'Queer Cast', 0),
(495, 'Queer Creator', 0),
(496, 'Queer', 0),
(497, 'Random', 0),
(498, 'Reader Driven', 0),
(499, 'Realism', 0),
(500, 'Redemption', 0),
(501, 'Reincarnation', 0),
(502, 'Religious', 0),
(503, 'Revenge', 0),
(504, 'Picaresque', 0),
(505, 'Romance', 0),
(506, 'Romantic Comedy', 0),
(507, 'RPG', 0),
(508, 'Russian Language', 0),
(509, 'Satire', 0),
(510, 'Saving the World', 0),
(512, 'Sequel', 0),
(513, 'Sex', 0),
(514, 'Short Stories', 0),
(515, 'No Dialogue', 0),
(516, 'Silver Age', 0),
(517, 'Slapstick', 0),
(518, 'Slice of Life', 0),
(520, 'Space Opera', 0),
(521, 'Space Travel', 0),
(522, 'Spanish Language', 0),
(523, 'Spider Forest', 1),
(524, 'Sports', 0),
(525, 'Sprite', 0),
(526, 'Spy', 0),
(527, 'Star-Crossed Lovers', 0),
(528, 'Steampunk', 0),
(529, 'Stick Figure', 0),
(530, 'Superhero', 0),
(531, 'Superpowers', 0),
(532, 'Surreal', 0),
(533, 'Suspense', 0),
(534, 'Sword and Sorcery', 0),
(535, 'Tapas', 0),
(536, 'The Duck', 0),
(537, 'Thieves', 0),
(538, 'Time Travel', 0),
(539, 'Tournament', 0),
(540, 'Traditional Media', 0),
(541, 'Tragedy', 0),
(542, 'Transgender', 0),
(543, 'Underworld', 0),
(544, 'Unreliable Narrator', 0),
(545, 'Urban', 0),
(546, 'Vampires', 0),
(547, 'Vertical Comic', 0),
(548, 'Video Game', 0),
(549, 'Virtual Reality', 0),
(550, 'Voice Acting', 0),
(551, 'Warfare', 0),
(552, 'Watercolor', 0),
(553, 'Webtoons', 0),
(554, 'Werewolves', 0),
(555, 'Western', 0),
(556, 'WoC', 0),
(557, 'Work', 0),
(558, 'Zombies', 0),
(559, 'Macabre', 0),
(560, 'Jungle', 0),
(561, 'Prehistoric', 0),
(562, 'StArt Faire', 1),
(563, 'Mobile-Friendly', 0),
(564, 'Shonen', 0),
(565, 'Shojo', 0),
(566, 'Seinen', 0),
(567, 'Josei', 0),
(568, 'KidLit', 0),
(569, 'Friendship', 0),
(570, '20th Century', 0),
(571, '21st Century', 0),
(572, 'Non-binary Creator', 0),
(573, 'Heartwarming', 0),
(574, 'Comicadia', 1),
(575, 'Pansexual', 0),
(576, 'Transcript Available', 0),
(577, 'Pokemon', 0);

-- --------------------------------------------------------

--
-- Table structure for table `urls`
--

CREATE TABLE `urls` (
  `url_id` int(11) NOT NULL,
  `url_host` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `url_ban` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `user_name` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `picture` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_digest` set('Daily','Weekly','Monthly','None') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'None',
  `claim_code` varchar(27) COLLATE utf8_unicode_ci NOT NULL,
  `site_settings` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_cookies`
--

CREATE TABLE `user_cookies` (
  `cookie_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `created` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_friends`
--

CREATE TABLE `user_friends` (
  `user_a_id` int(11) NOT NULL,
  `user_b_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_subs`
--

CREATE TABLE `user_subs` (
  `sub_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comic_id` int(11) NOT NULL,
  `bookmark` int(11) NOT NULL DEFAULT 0,
  `sub_type` set('public','private') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'public',
  `favorite` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warnings`
--

CREATE TABLE `warnings` (
  `warning_id` int(11) NOT NULL,
  `warning_name` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `warning_category` set('Adult Themes','Violence','Language','Nudity/Sex','Content') COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `warnings`
--

INSERT INTO `warnings` (`warning_id`, `warning_name`, `warning_category`) VALUES
(1, 'Profanity', 'Language'),
(2, 'Rape', 'Content'),
(3, 'Physical Abuse', 'Content'),
(4, 'Mental Abuse', 'Content'),
(5, 'Emotional Abuse', 'Content'),
(6, 'Verbal Abuse', 'Content'),
(7, 'Sexual Abuse', 'Content'),
(8, 'Child Abuse', 'Content'),
(9, 'Self Harm', 'Content'),
(10, 'Eating Disorders', 'Content'),
(11, 'Drug Use', 'Content'),
(12, 'Illegal Drug Use', 'Content'),
(13, 'Alcohol', 'Content'),
(14, 'Tobacco (Historial)', 'Content'),
(15, 'Tobacco', 'Content'),
(16, 'Suicide', 'Content'),
(17, 'Medical Procedures', 'Content'),
(18, 'Gore', 'Violence'),
(19, 'Violence', 'Violence'),
(20, 'Bladed Weapons', 'Violence'),
(21, 'Firearms', 'Violence'),
(22, 'Corpses', 'Content'),
(23, 'Skeletons', 'Content'),
(24, 'Needles', 'Content'),
(25, 'Racism', 'Content'),
(26, 'Sexism', 'Content'),
(27, 'Classism', 'Content'),
(28, 'Hatred', 'Adult Themes'),
(29, 'Homophobia', 'Content'),
(30, 'Transphobia', 'Content'),
(31, 'Acephobia', 'Content'),
(32, 'Lesbophobia', 'Content'),
(33, 'Biphobia', 'Content'),
(34, 'Misogyny', 'Content'),
(35, 'Misandry', 'Content'),
(36, 'Sex Shaming', 'Content'),
(37, 'Neuroatypical Shaming', 'Content'),
(38, 'Body Image/Shame', 'Content'),
(39, 'Oppression', 'Content'),
(40, 'Dismissal of Illness', 'Content'),
(41, 'Kidnapping', 'Content'),
(42, 'Death', 'Adult Themes'),
(43, 'Blood', 'Content'),
(44, 'Spiders', 'Content'),
(45, 'Snakes', 'Content'),
(46, 'Insects', 'Content'),
(47, 'Vomit', 'Content'),
(48, 'Pregnancy/Childbirth', 'Content'),
(49, 'Serious Injury', 'Violence'),
(50, 'Trypophobia', 'Content'),
(51, 'Scarification', 'Content'),
(52, 'Nazis', 'Content'),
(53, 'Slime/Ooze', 'Content'),
(54, 'OCD Triggers', 'Content'),
(55, 'Pedophilia', 'Content'),
(56, 'Discrimination', 'Adult Themes'),
(57, 'Abortion', 'Content'),
(58, 'Sex', 'Nudity/Sex'),
(59, 'Nudity (Non-sexual)', 'Nudity/Sex'),
(60, 'Nudity (Sexual)', 'Nudity/Sex'),
(61, 'Miscarriage', 'Content'),
(62, 'Dismissal of Disability', 'Content'),
(63, 'Car Collision', 'Content'),
(64, 'War', 'Adult Themes'),
(65, 'Natural Disaster', 'Content'),
(66, 'Burglary', 'Content'),
(67, 'Bullying', 'Content'),
(68, 'Drowning', 'Content'),
(69, 'Stalking', 'Content'),
(70, 'Gaslighting', 'Content'),
(71, 'Narcissism', 'Content'),
(72, 'Animal Abuse', 'Content'),
(73, 'Cult', 'Content'),
(74, 'Brainwashing', 'Content'),
(75, 'Terrorism', 'Violence'),
(76, 'Explosions', 'Violence'),
(77, 'Arophobia', 'Content'),
(78, 'Eye Trauma', 'Violence'),
(79, 'Flashing Images', 'Content'),
(80, 'Incest', 'Content'),
(81, 'Plays Sound/Music', 'Content'),
(82, 'Crude Humor', 'Adult Themes'),
(83, 'Sexual Humor', 'Nudity/Sex'),
(84, 'Sexual Themes', 'Nudity/Sex'),
(85, 'Human Trafficking', 'Content');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comics`
--
ALTER TABLE `comics`
  ADD PRIMARY KEY (`comic_id`),
  ADD KEY `comic_id` (`comic_id`);

--
-- Indexes for table `comic_owners`
--
ALTER TABLE `comic_owners`
  ADD PRIMARY KEY (`relation_id`),
  ADD KEY `comic_id` (`comic_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `comic_rating`
--
ALTER TABLE `comic_rating`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `comic_id` (`comic_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `comic_tags`
--
ALTER TABLE `comic_tags`
  ADD PRIMARY KEY (`relation_id`),
  ADD KEY `comic_id` (`comic_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `comic_warnings`
--
ALTER TABLE `comic_warnings`
  ADD PRIMARY KEY (`relation_id`),
  ADD KEY `comic_id` (`comic_id`),
  ADD KEY `warning_id` (`warning_id`);

--
-- Indexes for table `locked_comics`
--
ALTER TABLE `locked_comics`
  ADD KEY `comic_id` (`comic_id`);

--
-- Indexes for table `oauth_ids`
--
ALTER TABLE `oauth_ids`
  ADD PRIMARY KEY (`auth_index`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`tag_id`);

--
-- Indexes for table `urls`
--
ALTER TABLE `urls`
  ADD PRIMARY KEY (`url_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_cookies`
--
ALTER TABLE `user_cookies`
  ADD PRIMARY KEY (`cookie_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_friends`
--
ALTER TABLE `user_friends`
  ADD KEY `user_a_id` (`user_a_id`),
  ADD KEY `user_b_id` (`user_b_id`);

--
-- Indexes for table `user_subs`
--
ALTER TABLE `user_subs`
  ADD PRIMARY KEY (`sub_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `comic_id` (`comic_id`);

--
-- Indexes for table `warnings`
--
ALTER TABLE `warnings`
  ADD PRIMARY KEY (`warning_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comics`
--
ALTER TABLE `comics`
  MODIFY `comic_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comic_owners`
--
ALTER TABLE `comic_owners`
  MODIFY `relation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comic_rating`
--
ALTER TABLE `comic_rating`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `comic_tags`
--
ALTER TABLE `comic_tags`
  MODIFY `relation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comic_warnings`
--
ALTER TABLE `comic_warnings`
  MODIFY `relation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oauth_ids`
--
ALTER TABLE `oauth_ids`
  MODIFY `auth_index` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=578;

--
-- AUTO_INCREMENT for table `urls`
--
ALTER TABLE `urls`
  MODIFY `url_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_cookies`
--
ALTER TABLE `user_cookies`
  MODIFY `cookie_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_subs`
--
ALTER TABLE `user_subs`
  MODIFY `sub_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `warnings`
--
ALTER TABLE `warnings`
  MODIFY `warning_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comic_owners`
--
ALTER TABLE `comic_owners`
  ADD CONSTRAINT `comic_owners_ibfk_1` FOREIGN KEY (`comic_id`) REFERENCES `comics` (`comic_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comic_owners_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comic_rating`
--
ALTER TABLE `comic_rating`
  ADD CONSTRAINT `comic_rating_ibfk_1` FOREIGN KEY (`comic_id`) REFERENCES `comics` (`comic_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comic_rating_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comic_tags`
--
ALTER TABLE `comic_tags`
  ADD CONSTRAINT `comic_tags_ibfk_1` FOREIGN KEY (`comic_id`) REFERENCES `comics` (`comic_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comic_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comic_warnings`
--
ALTER TABLE `comic_warnings`
  ADD CONSTRAINT `comic_warnings_ibfk_1` FOREIGN KEY (`comic_id`) REFERENCES `comics` (`comic_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comic_warnings_ibfk_2` FOREIGN KEY (`warning_id`) REFERENCES `warnings` (`warning_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `locked_comics`
--
ALTER TABLE `locked_comics`
  ADD CONSTRAINT `locked_comics_ibfk_1` FOREIGN KEY (`comic_id`) REFERENCES `comics` (`comic_id`);

--
-- Constraints for table `oauth_ids`
--
ALTER TABLE `oauth_ids`
  ADD CONSTRAINT `oauth_ids_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_cookies`
--
ALTER TABLE `user_cookies`
  ADD CONSTRAINT `user_cookies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_friends`
--
ALTER TABLE `user_friends`
  ADD CONSTRAINT `user_friends_ibfk_1` FOREIGN KEY (`user_a_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_friends_ibfk_2` FOREIGN KEY (`user_b_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_subs`
--
ALTER TABLE `user_subs`
  ADD CONSTRAINT `user_subs_ibfk_1` FOREIGN KEY (`comic_id`) REFERENCES `comics` (`comic_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_subs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

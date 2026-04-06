-- phpMyAdmin SQL Dump
-- version 2.6.4-pl3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost:3306
-- Generatie Tijd: 22 Jun 2014 om 17:02
-- Server versie: 4.1.20
-- PHP Versie: 5.0.5
-- 
-- Database: `poule`
-- 

-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `city`
-- 

CREATE TABLE `city` (
  `city_id` int(11) NOT NULL auto_increment,
  `city_name` varchar(45) default NULL,
  `Competition_competition_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`city_id`),
  KEY `Competition_competition_id` (`Competition_competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `city`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `competition`
-- 

CREATE TABLE `competition` (
  `competition_id` int(11) NOT NULL auto_increment,
  `competition_name` text NOT NULL,
  `competition_description` text NOT NULL,
  `competition_header` text NOT NULL,
  `competition_money` int(11) NOT NULL default '0',
  `competition_first_place` int(11) NOT NULL default '0',
  `competition_second_place` int(11) NOT NULL default '0',
  `competition_third_place` int(11) NOT NULL default '0',
  `competition_final_submission_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `competition`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `component`
-- 

CREATE TABLE `component` (
  `com_id` int(11) NOT NULL auto_increment,
  `com_name` varchar(45) default NULL,
  `com_friendlyname` varchar(45) default NULL,
  `com_in_menu` int(11) NOT NULL default '0',
  `com_menu_parent` int(11) NOT NULL default '0',
  `com_defrights` int(11) NOT NULL default '0',
  `com_defchrights` int(11) NOT NULL default '0',
  PRIMARY KEY  (`com_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `component`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `country`
-- 

CREATE TABLE `country` (
  `country_id` int(11) NOT NULL auto_increment,
  `country_name` varchar(45) default NULL,
  `country_flag` varchar(45) default NULL,
  `Competition_competition_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`country_id`),
  KEY `Competition_competition_id` (`Competition_competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=33 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `country`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `form`
-- 

CREATE TABLE `form` (
  `form_id` int(11) NOT NULL auto_increment,
  `form_name` text NOT NULL,
  `form_file` text NOT NULL,
  `Competition_competition_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`form_id`),
  KEY `Competition_competition_id` (`Competition_competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `form`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `game`
-- 

CREATE TABLE `game` (
  `game_id` int(11) NOT NULL auto_increment,
  `game_date` varchar(45) default NULL,
  `game_result` varchar(45) default NULL,
  `game_red_cards` varchar(45) default NULL,
  `game_yellow_cards` varchar(45) default NULL,
  `City_city_id` int(11) NOT NULL default '0',
  `Country_country_id_home` int(11) NOT NULL default '0',
  `Country_country_id_away` int(11) NOT NULL default '0',
  `Poule_poule_id` int(11) NOT NULL default '0',
  `Competition_competition_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`game_id`),
  KEY `fk_Game_City1_idx` (`City_city_id`),
  KEY `fk_Game_Country1_idx` (`Country_country_id_home`),
  KEY `fk_Game_Country2_idx` (`Country_country_id_away`),
  KEY `fk_Game_Poule1_idx` (`Poule_poule_id`),
  KEY `Competition_competition_id` (`Competition_competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `game`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `participant`
-- 

CREATE TABLE `participant` (
  `part_postalCode` varchar(6) default NULL,
  `part_street` varchar(100) default NULL,
  `part_town` varchar(100) default NULL,
  `part_housenr` int(11) default NULL,
  `part_addition` varchar(10) default NULL,
  `part_bankaccount` varchar(30) default NULL,
  `User_user_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`User_user_id`),
  KEY `fk_Customer_User` (`User_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Gegevens worden uitgevoerd voor tabel `participant`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `participant_competition`
-- 

CREATE TABLE `participant_competition` (
  `Participant_User_user_id` int(11) NOT NULL default '0',
  `Competition_competition_id` int(11) NOT NULL default '0',
  `Participant_Competition_payed` int(11) NOT NULL default '0',
  `Participant_Competition_subscribed` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Participant_User_user_id`,`Competition_competition_id`),
  UNIQUE KEY `User_user_id_3` (`Participant_User_user_id`,`Competition_competition_id`),
  UNIQUE KEY `Participant_User_user_id` (`Participant_User_user_id`,`Competition_competition_id`),
  KEY `User_user_id` (`Participant_User_user_id`,`Competition_competition_id`),
  KEY `User_user_id_2` (`Participant_User_user_id`),
  KEY `Competition_competition_id` (`Competition_competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Gegevens worden uitgevoerd voor tabel `participant_competition`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `participant_game_prediction`
-- 

CREATE TABLE `participant_game_prediction` (
  `Participant_User_user_id` int(11) NOT NULL default '0',
  `Game_game_id` int(11) NOT NULL default '0',
  `Participant_Game_result` varchar(45) default NULL,
  `Participant_Game_red_cards` int(11) NOT NULL default '0',
  `Participant_Game_yellow_cards` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Participant_User_user_id`,`Game_game_id`),
  KEY `fk_Participant_has_Game_Game1_idx` (`Game_game_id`),
  KEY `fk_Participant_has_Game_Participant1_idx` (`Participant_User_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Gegevens worden uitgevoerd voor tabel `participant_game_prediction`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `participant_question_prediction`
-- 

CREATE TABLE `participant_question_prediction` (
  `Participant_User_user_id` int(11) NOT NULL default '0',
  `Question_question_id` int(11) NOT NULL default '0',
  `Participant_Question_answer` varchar(45) default NULL,
  PRIMARY KEY  (`Participant_User_user_id`,`Question_question_id`),
  KEY `fk_Participant_has_Question_Question1_idx` (`Question_question_id`),
  KEY `fk_Participant_has_Question_Participant1_idx` (`Participant_User_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Gegevens worden uitgevoerd voor tabel `participant_question_prediction`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `participant_round_prediction`
-- 

CREATE TABLE `participant_round_prediction` (
  `Participant_User_user_id` int(11) NOT NULL default '0',
  `Round_round_id` int(11) NOT NULL default '0',
  `Round_prediction_id` int(11) NOT NULL default '0',
  `Country_country_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Participant_User_user_id`,`Round_round_id`,`Round_prediction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Gegevens worden uitgevoerd voor tabel `participant_round_prediction`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `participant_subleague`
-- 

CREATE TABLE `participant_subleague` (
  `Participant_User_user_id` int(11) NOT NULL default '0',
  `Subleague_subleague_id` int(11) NOT NULL default '0',
  UNIQUE KEY `Participant_User_user_id` (`Participant_User_user_id`,`Subleague_subleague_id`),
  KEY `Participant_participant_id` (`Participant_User_user_id`,`Subleague_subleague_id`),
  KEY `Subleague_subleague_id` (`Subleague_subleague_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Gegevens worden uitgevoerd voor tabel `participant_subleague`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `player`
-- 

CREATE TABLE `player` (
  `player_id` int(11) NOT NULL auto_increment,
  `player_name` text NOT NULL,
  `Country_country_id` int(11) NOT NULL default '0',
  `Competition_competition_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`player_id`),
  KEY `Competition_competition_id` (`Competition_competition_id`),
  KEY `Country_country_id` (`Country_country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=743 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `player`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `poule`
-- 

CREATE TABLE `poule` (
  `poule_id` int(11) NOT NULL auto_increment,
  `poule_name` varchar(45) default NULL,
  `Competition_competition_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`poule_id`),
  KEY `Competition_competition_id` (`Competition_competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `poule`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `question`
-- 

CREATE TABLE `question` (
  `question_id` int(11) NOT NULL auto_increment,
  `question_question` text,
  `question_type` varchar(45) NOT NULL default '',
  `question_anwser_count` int(11) NOT NULL default '1',
  `question_anwser` varchar(500) NOT NULL default '',
  `Competition_competition_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`question_id`),
  KEY `Competition_competition_id` (`Competition_competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `question`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `referee`
-- 

CREATE TABLE `referee` (
  `referee_id` int(11) NOT NULL auto_increment,
  `referee_name` text NOT NULL,
  `Competition_competition_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`referee_id`),
  KEY `Competition_competition_id` (`Competition_competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=35 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `referee`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `rights`
-- 

CREATE TABLE `rights` (
  `Component_com_id` int(11) NOT NULL default '0',
  `UserGroup_group_id` int(11) NOT NULL default '0',
  `rights` int(11) default NULL,
  `rightnochange` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Component_com_id`,`UserGroup_group_id`),
  KEY `fk_Component_has_UserGroup_Component` (`Component_com_id`),
  KEY `fk_Component_has_UserGroup_UserGroup` (`UserGroup_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Gegevens worden uitgevoerd voor tabel `rights`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `round`
-- 

CREATE TABLE `round` (
  `round_id` int(11) NOT NULL auto_increment,
  `round_name` varchar(45) default NULL,
  `round_count` int(11) NOT NULL default '0',
  `Competition_competition_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`round_id`),
  KEY `Competition_competition_id` (`Competition_competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `round`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `round_result`
-- 

CREATE TABLE `round_result` (
  `round_result_id` int(11) NOT NULL auto_increment,
  `Country_country_id` int(11) NOT NULL default '0',
  `Round_round_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`round_result_id`),
  KEY `fk_Country_has_Round_Round1_idx` (`Round_round_id`),
  KEY `fk_Country_has_Round_Country1_idx` (`Country_country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3585 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `round_result`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `scoring`
-- 

CREATE TABLE `scoring` (
  `scoring_id` int(11) NOT NULL auto_increment,
  `scoring_name` text NOT NULL,
  `Section_section_id` int(11) NOT NULL default '0',
  `Round_round_id` int(11) NOT NULL default '0',
  `Competition_competition_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`scoring_id`),
  KEY `Section_section_id` (`Section_section_id`),
  KEY `Round_round_id` (`Round_round_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `scoring`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `scoring_competition`
-- 

CREATE TABLE `scoring_competition` (
  `Scoring_scoring_id` int(11) NOT NULL default '0',
  `Competition_competition_id` int(11) NOT NULL default '0',
  `Scoring_Competition_enabled` int(11) NOT NULL default '0',
  `Scoring_Competition_points` int(11) NOT NULL default '0',
  `Round_round_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Scoring_scoring_id`,`Competition_competition_id`),
  KEY `Round_round_id` (`Round_round_id`),
  KEY `Competition_competition_id` (`Competition_competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Gegevens worden uitgevoerd voor tabel `scoring_competition`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `section`
-- 

CREATE TABLE `section` (
  `section_id` int(11) NOT NULL auto_increment,
  `section_name` varchar(45) NOT NULL default '',
  PRIMARY KEY  (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `section`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `section_competition`
-- 

CREATE TABLE `section_competition` (
  `Section_section_id` int(11) NOT NULL default '0',
  `Competition_competition_id` int(11) NOT NULL default '0',
  `Section_Competition_enabled` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Section_section_id`,`Competition_competition_id`),
  UNIQUE KEY `Section_section_id` (`Section_section_id`,`Competition_competition_id`),
  KEY `Competition_competition_id` (`Competition_competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Gegevens worden uitgevoerd voor tabel `section_competition`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `subleague`
-- 

CREATE TABLE `subleague` (
  `subleague_id` int(11) NOT NULL auto_increment,
  `subleague_name` text NOT NULL,
  `subleague_header` text NOT NULL,
  `Competition_competition_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`subleague_id`),
  KEY `Competition_competition_id` (`Competition_competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `subleague`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `table`
-- 

CREATE TABLE `table` (
  `Participant_User_user_id` int(11) NOT NULL default '0',
  `Competition_competition_id` int(11) NOT NULL default '0',
  `table_points` int(11) NOT NULL default '0',
  `table_position` int(11) NOT NULL default '0',
  `table_old_position` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Participant_User_user_id`,`Competition_competition_id`),
  KEY `fk_Table_Participant1_idx` (`Participant_User_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Gegevens worden uitgevoerd voor tabel `table`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `user`
-- 

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL auto_increment,
  `user_email` varchar(128) default NULL,
  `user_enabled` tinyint(4) NOT NULL default '0',
  `user_firstname` varchar(45) default NULL,
  `user_lastname` varchar(70) default NULL,
  `user_password` varchar(65) default NULL,
  `user_temp_password` varchar(65) default NULL,
  `user_phonenr` varchar(45) default NULL,
  `user_lastlogin` int(11) default NULL,
  `user_logincount` int(11) default NULL,
  `UserGroup_group_id` int(11) default NULL,
  PRIMARY KEY  (`user_id`),
  KEY `fk_User_UserGroup` (`UserGroup_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=237 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `user`
-- 


-- --------------------------------------------------------

-- 
-- Tabel structuur voor tabel `usergroup`
-- 

CREATE TABLE `usergroup` (
  `group_id` int(11) NOT NULL auto_increment,
  `group_name` varchar(45) default NULL,
  `group_readonly` int(11) default NULL,
  PRIMARY KEY  (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- 
-- Gegevens worden uitgevoerd voor tabel `usergroup`
-- 


-- 
-- Beperkingen voor gedumpte tabellen
-- 

-- 
-- Beperkingen voor tabel `city`
-- 
ALTER TABLE `city`
  ADD CONSTRAINT `city_ibfk_1` FOREIGN KEY (`Competition_competition_id`) REFERENCES `competition` (`competition_id`);

-- 
-- Beperkingen voor tabel `form`
-- 
ALTER TABLE `form`
  ADD CONSTRAINT `form_ibfk_1` FOREIGN KEY (`Competition_competition_id`) REFERENCES `competition` (`competition_id`);

-- 
-- Beperkingen voor tabel `game`
-- 
ALTER TABLE `game`
  ADD CONSTRAINT `fk_Game_City1` FOREIGN KEY (`City_city_id`) REFERENCES `city` (`city_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Game_Country1` FOREIGN KEY (`Country_country_id_home`) REFERENCES `country` (`country_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Game_Country2` FOREIGN KEY (`Country_country_id_away`) REFERENCES `country` (`country_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Game_Poule1` FOREIGN KEY (`Poule_poule_id`) REFERENCES `poule` (`poule_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `game_ibfk_1` FOREIGN KEY (`Competition_competition_id`) REFERENCES `competition` (`competition_id`);

-- 
-- Beperkingen voor tabel `participant`
-- 
ALTER TABLE `participant`
  ADD CONSTRAINT `fk_Customer_User` FOREIGN KEY (`User_user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- 
-- Beperkingen voor tabel `participant_competition`
-- 
ALTER TABLE `participant_competition`
  ADD CONSTRAINT `participant_competition_ibfk_2` FOREIGN KEY (`Competition_competition_id`) REFERENCES `competition` (`competition_id`),
  ADD CONSTRAINT `participant_competition_ibfk_3` FOREIGN KEY (`Participant_User_user_id`) REFERENCES `participant` (`User_user_id`);

-- 
-- Beperkingen voor tabel `participant_game_prediction`
-- 
ALTER TABLE `participant_game_prediction`
  ADD CONSTRAINT `fk_Participant_has_Game_Game1` FOREIGN KEY (`Game_game_id`) REFERENCES `game` (`game_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Participant_has_Game_Participant1` FOREIGN KEY (`Participant_User_user_id`) REFERENCES `participant` (`User_user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- 
-- Beperkingen voor tabel `participant_question_prediction`
-- 
ALTER TABLE `participant_question_prediction`
  ADD CONSTRAINT `fk_Participant_has_Question_Participant1` FOREIGN KEY (`Participant_User_user_id`) REFERENCES `participant` (`User_user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Participant_has_Question_Question1` FOREIGN KEY (`Question_question_id`) REFERENCES `question` (`question_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- 
-- Beperkingen voor tabel `participant_subleague`
-- 
ALTER TABLE `participant_subleague`
  ADD CONSTRAINT `participant_subleague_ibfk_1` FOREIGN KEY (`Participant_User_user_id`) REFERENCES `participant` (`User_user_id`),
  ADD CONSTRAINT `participant_subleague_ibfk_2` FOREIGN KEY (`Subleague_subleague_id`) REFERENCES `subleague` (`subleague_id`);

-- 
-- Beperkingen voor tabel `player`
-- 
ALTER TABLE `player`
  ADD CONSTRAINT `player_ibfk_1` FOREIGN KEY (`Competition_competition_id`) REFERENCES `competition` (`competition_id`),
  ADD CONSTRAINT `player_ibfk_2` FOREIGN KEY (`Country_country_id`) REFERENCES `country` (`country_id`);

-- 
-- Beperkingen voor tabel `poule`
-- 
ALTER TABLE `poule`
  ADD CONSTRAINT `poule_ibfk_1` FOREIGN KEY (`Competition_competition_id`) REFERENCES `competition` (`competition_id`);

-- 
-- Beperkingen voor tabel `question`
-- 
ALTER TABLE `question`
  ADD CONSTRAINT `question_ibfk_1` FOREIGN KEY (`Competition_competition_id`) REFERENCES `competition` (`competition_id`);

-- 
-- Beperkingen voor tabel `referee`
-- 
ALTER TABLE `referee`
  ADD CONSTRAINT `referee_ibfk_1` FOREIGN KEY (`Competition_competition_id`) REFERENCES `competition` (`competition_id`);

-- 
-- Beperkingen voor tabel `rights`
-- 
ALTER TABLE `rights`
  ADD CONSTRAINT `fk_Component_has_UserGroup_Component` FOREIGN KEY (`Component_com_id`) REFERENCES `component` (`com_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Component_has_UserGroup_UserGroup` FOREIGN KEY (`UserGroup_group_id`) REFERENCES `usergroup` (`group_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- 
-- Beperkingen voor tabel `round`
-- 
ALTER TABLE `round`
  ADD CONSTRAINT `round_ibfk_1` FOREIGN KEY (`Competition_competition_id`) REFERENCES `competition` (`competition_id`);

-- 
-- Beperkingen voor tabel `round_result`
-- 
ALTER TABLE `round_result`
  ADD CONSTRAINT `fk_Country_has_Round_Round1` FOREIGN KEY (`Round_round_id`) REFERENCES `round` (`round_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- 
-- Beperkingen voor tabel `scoring`
-- 
ALTER TABLE `scoring`
  ADD CONSTRAINT `scoring_ibfk_1` FOREIGN KEY (`Section_section_id`) REFERENCES `section` (`section_id`);

-- 
-- Beperkingen voor tabel `scoring_competition`
-- 
ALTER TABLE `scoring_competition`
  ADD CONSTRAINT `scoring_competition_ibfk_1` FOREIGN KEY (`Scoring_scoring_id`) REFERENCES `scoring` (`scoring_id`),
  ADD CONSTRAINT `scoring_competition_ibfk_2` FOREIGN KEY (`Competition_competition_id`) REFERENCES `competition` (`competition_id`);

-- 
-- Beperkingen voor tabel `section_competition`
-- 
ALTER TABLE `section_competition`
  ADD CONSTRAINT `section_competition_ibfk_1` FOREIGN KEY (`Section_section_id`) REFERENCES `section` (`section_id`),
  ADD CONSTRAINT `section_competition_ibfk_2` FOREIGN KEY (`Competition_competition_id`) REFERENCES `competition` (`competition_id`);

-- 
-- Beperkingen voor tabel `subleague`
-- 
ALTER TABLE `subleague`
  ADD CONSTRAINT `subleague_ibfk_1` FOREIGN KEY (`Competition_competition_id`) REFERENCES `competition` (`competition_id`);

-- 
-- Beperkingen voor tabel `table`
-- 
ALTER TABLE `table`
  ADD CONSTRAINT `fk_Table_Participant1` FOREIGN KEY (`Participant_User_user_id`) REFERENCES `participant` (`User_user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- 
-- Beperkingen voor tabel `user`
-- 
ALTER TABLE `user`
  ADD CONSTRAINT `fk_User_UserGroup` FOREIGN KEY (`UserGroup_group_id`) REFERENCES `usergroup` (`group_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

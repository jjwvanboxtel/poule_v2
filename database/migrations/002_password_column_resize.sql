-- Migration 002: Widen password columns to accommodate Argon2id hashes (~97 chars)
ALTER TABLE `user`
  MODIFY COLUMN `user_password` varchar(255) default NULL,
  MODIFY COLUMN `user_temp_password` varchar(255) default NULL;

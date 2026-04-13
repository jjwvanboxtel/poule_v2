-- Migration: Increase question_anwser column size
-- Date: 2026-04-05
-- Reason: VARCHAR(45) is too small for questions with multiple comma-separated answers

ALTER TABLE `question` 
MODIFY COLUMN `question_anwser` VARCHAR(500) NOT NULL DEFAULT '';

-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 21, 2025 at 01:24 AM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quiz`
--

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `id` int(11) NOT NULL,
  `educatorID` int(11) NOT NULL,
  `topicID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`id`, `educatorID`, `topicID`) VALUES
(34, 45, 3),
(35, 45, 4),
(39, 51, 1);

-- --------------------------------------------------------

--
-- Table structure for table `quizfeedback`
--

CREATE TABLE `quizfeedback` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comments` text NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `quizfeedback`
--

INSERT INTO `quizfeedback` (`id`, `quizID`, `rating`, `comments`, `date`) VALUES
(7, 34, 5, 'It was an easy quiz, I liked it ❤️', '2025-11-09 17:14:24'),
(8, 35, 2, 'Some questions were a bit tricky !', '2025-11-09 17:15:34'),
(9, 39, 4, 'Fun and educational, thank you!', '2025-11-09 17:18:07'),
(10, 35, 5, 'It was interesting and clear', '2025-11-09 17:21:27'),
(11, 34, 3, 'good', '2025-11-11 12:15:32');

-- --------------------------------------------------------

--
-- Table structure for table `quizquestion`
--

CREATE TABLE `quizquestion` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `questionFigureFileName` varchar(255) NOT NULL,
  `answerA` varchar(255) NOT NULL,
  `answerB` varchar(255) NOT NULL,
  `answerC` varchar(255) NOT NULL,
  `answerD` varchar(255) NOT NULL,
  `correctAnswer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `quizquestion`
--

INSERT INTO `quizquestion` (`id`, `quizID`, `question`, `questionFigureFileName`, `answerA`, `answerB`, `answerC`, `answerD`, `correctAnswer`) VALUES
(20, 34, 'What is the first thing you should do when you see someone injured?', 'question_1762696268_69109c4c1aacd.webp', 'Check if the scene is safe', ' Move the person away immediately', 'Start CPR right away', 'Give them something to drink', 'A'),
(21, 34, 'What should you do if someone is bleeding heavily from their arm?', 'question_1762696475_69109d1b78495.jpg', 'Wash the wound with cold water', 'Apply direct pressure on the wound', 'Remove any objects stuck in the wound', 'Wait for medical help without touching it', 'B'),
(22, 35, 'How much sleep do most adults need each night to stay healthy?', 'question_1762696861_69109e9d7f503.jpg', 'About 3 hours', 'About 5 hours', 'About 7–9 hours', 'About 10–12 hours', 'C'),
(23, 35, 'What is one benefit of drinking enough water every day?', 'question_1762697045_69109f553a99e.webp', 'It causes tiredness', 'It keeps the body hydrated', 'It increases stress', 'It weakens the muscles', 'B'),
(24, 39, 'Which nutrient gives your body energy?', 'question_1762697257_6910a0290c860.webp', 'Vitamins', 'Carbohydrates', 'Water', 'Minerals', 'B'),
(25, 39, 'What is a good source of calcium?', 'question_1762697358_6910a08e86cdc.jpg', 'Milk', 'Chicken', 'Apples', 'Bread', 'A'),
(26, 39, 'Why does your body need protein?', 'question_1762697448_6910a0e837666.webp', 'To provide flavor', 'To increase sugar levels', 'To store fat', 'To build and repair tissues', 'D'),
(27, 34, 'What should you do if someone has a small burn?', 'QIMG_6910a4af9f2f69.75394870.webp', 'Cover it with ice', 'Wash it with cool running water', 'Apply butter', 'Pop any blisters', 'B'),
(28, 35, 'How often should you brush your teeth for good hygiene ?', 'QIMG_6910a5a997a356.94491214.webp', 'Once a week', 'Once a day', 'Twice a day', 'Only before sleeping', 'C'),
(29, 39, 'Which food is high in vitamin C ?', 'QIMG_6910a543154720.79158820.webp', 'Oranges', 'Bread', 'Cheese', 'Meat', 'A'),
(30, 39, 'What does fiber help with in the body ?', 'QIMG_6910b3e3095526.93766422.webp', 'Digestion', 'Breathing', 'Blood pressure', 'Vision', 'A');

-- --------------------------------------------------------

--
-- Table structure for table `recommendedquestion`
--

CREATE TABLE `recommendedquestion` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `learnerID` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `questionFigureFileName` varchar(255) NOT NULL,
  `answerA` varchar(255) NOT NULL,
  `answerB` varchar(255) NOT NULL,
  `answerC` varchar(255) NOT NULL,
  `answerD` varchar(255) NOT NULL,
  `correctAnswer` enum('A','B','C','D') NOT NULL,
  `status` enum('pending','approved','disapproved') NOT NULL,
  `comments` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `recommendedquestion`
--

INSERT INTO `recommendedquestion` (`id`, `quizID`, `learnerID`, `question`, `questionFigureFileName`, `answerA`, `answerB`, `answerC`, `answerD`, `correctAnswer`, `status`, `comments`) VALUES
(16, 34, 48, 'What should you do if someone has a small burn?', 'QIMG_6910a4af9f2f69.75394870.webp', 'Cover it with ice', 'Wash it with cool running water', 'Apply butter', 'Pop any blisters', 'B', 'approved', 'Good question! This is very relevant to First Aid. I’m adding it to the quiz'),
(17, 39, 48, 'Which food is high in vitamin C ?', 'QIMG_6910a543154720.79158820.webp', 'Oranges', 'Bread', 'Cheese', 'Meat', 'A', 'approved', 'Great question! It fits perfectly with the Nutrition topic. Well done'),
(18, 35, 48, 'How often should you brush your teeth for good hygiene ?', 'QIMG_6910a5a997a356.94491214.webp', 'Once a week', 'Once a day', 'Twice a day', 'Only before sleeping', 'C', 'approved', 'Excellent choice! This helps reinforce healthy habits.'),
(19, 35, 46, 'Which of the following is a healthy sleep habit ?', 'QIMG_6910a6316cab36.33301988.webp', 'Using your phone in bed', 'Sleeping at irregular times', 'Getting 7–8 hours of sleep each night', 'Drinking coffee before bed', 'C', 'disapproved', 'Interesting question, but it’s unrelated to the course topics, so I won’t include it'),
(21, 39, 46, 'What does fiber help with in the body ?', 'QIMG_6910b3e3095526.93766422.webp', 'Digestion', 'Breathing', 'Blood pressure', 'Vision', 'A', 'approved', 'Great suggestion! This question helps reinforce important concepts, thank you ❤️');

-- --------------------------------------------------------

--
-- Table structure for table `takenquiz`
--

CREATE TABLE `takenquiz` (
  `id` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `score` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `takenquiz`
--

INSERT INTO `takenquiz` (`id`, `quizID`, `score`) VALUES
(54, 34, 100),
(55, 35, 50),
(56, 39, 100),
(57, 39, 67),
(58, 35, 100),
(59, 35, 100),
(60, 35, 100),
(61, 35, 100),
(62, 34, 33),
(63, 34, 100);

-- --------------------------------------------------------

--
-- Table structure for table `topic`
--

CREATE TABLE `topic` (
  `id` int(11) NOT NULL,
  `topicName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `topic`
--

INSERT INTO `topic` (`id`, `topicName`) VALUES
(1, 'Nutrition'),
(2, 'Mental Health'),
(3, 'First Aid'),
(4, 'Healthy Habits');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `emailAddress` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photoFileName` varchar(255) NOT NULL,
  `userType` enum('learner','educator') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `firstName`, `lastName`, `emailAddress`, `password`, `photoFileName`, `userType`) VALUES
(45, 'Aseel', 'Almubaddel', 'Aseel@gmail.com', '$2y$10$SwoFOHU04oYZ8nCxD9eKMOj6WsW8ictv6qwE8FlU1mLMUKhK2K/M2', 'user_69109645bf03b0.97545857.jpg', 'educator'),
(46, 'Tala', 'Alsheail', 'Tala@gmail.com', '$2y$10$u0DydFqRDw3JxeJExG7vEetWPe0gh1kQIPfA3AtvGzj0UxIEdRgem', 'user_6910972073ab00.68079874.webp', 'learner'),
(48, 'Alanoud', 'Aloraydi', 'Alanoud@gmail.com', '$2y$10$EC1nKAE5HwffPx0aSE794OBRwz2thPgClscpq4.OwKBRK3Go0Prpy', 'user_6910991a3752c6.74328217.jpg', 'learner'),
(51, 'Norah', 'Aldalal', 'Norah@gmail.com', '$2y$10$n08Q.UtHLw0OTOULT85EcOFNfcTyItwBJrpq1ffrLNVCOvwEniek.', 'user_69109b0f96c963.37196604.webp', 'educator');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `educatorID` (`educatorID`),
  ADD KEY `topicID` (`topicID`);

--
-- Indexes for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizID` (`quizID`);

--
-- Indexes for table `quizquestion`
--
ALTER TABLE `quizquestion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizID` (`quizID`);

--
-- Indexes for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizID` (`quizID`),
  ADD KEY `learnerID` (`learnerID`);

--
-- Indexes for table `takenquiz`
--
ALTER TABLE `takenquiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizID` (`quizID`);

--
-- Indexes for table `topic`
--
ALTER TABLE `topic`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `quizquestion`
--
ALTER TABLE `quizquestion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `takenquiz`
--
ALTER TABLE `takenquiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `topic`
--
ALTER TABLE `topic`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`educatorID`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `quiz_ibfk_2` FOREIGN KEY (`topicID`) REFERENCES `topic` (`id`);

--
-- Constraints for table `quizfeedback`
--
ALTER TABLE `quizfeedback`
  ADD CONSTRAINT `quizfeedback_ibfk_1` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`);

--
-- Constraints for table `quizquestion`
--
ALTER TABLE `quizquestion`
  ADD CONSTRAINT `quizquestion_ibfk_1` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`);

--
-- Constraints for table `recommendedquestion`
--
ALTER TABLE `recommendedquestion`
  ADD CONSTRAINT `recommendedquestion_ibfk_1` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`),
  ADD CONSTRAINT `recommendedquestion_ibfk_2` FOREIGN KEY (`learnerID`) REFERENCES `user` (`id`);

--
-- Constraints for table `takenquiz`
--
ALTER TABLE `takenquiz`
  ADD CONSTRAINT `takenquiz_ibfk_1` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

--
-- Table structure for table `polls`
--

CREATE TABLE `polls` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `polls`
--

INSERT INTO `polls` (`id`, `question`, `created`) VALUES
(2, 'does this still work', '2020-04-28 10:47:14'),
(3, 'does this still work', '2020-04-28 12:08:55');

-- --------------------------------------------------------

--
-- Table structure for table `poll_options`
--

CREATE TABLE `poll_options` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `answer_text` varchar(255) NOT NULL,
  `answer_alias` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `poll_options`
--

INSERT INTO `poll_options` (`id`, `poll_id`, `answer_text`, `answer_alias`) VALUES
(1, 2, 'yes', ''),
(2, 2, 'no', ''),
(3, 3, 'yes', 'yes'),
(4, 3, 'no', 'no'),
(6, 4, 'yes', 'yes'),
(7, 4, 'no', 'no');

-- --------------------------------------------------------

--
-- Table structure for table `poll_responses`
--

CREATE TABLE `poll_responses` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `response_id` varchar(255) NOT NULL,
  `user_ip` varchar(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 11, 2026 at 11:52 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medicaldata`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_reply` text DEFAULT NULL,
  `status` enum('Pending','Resolved') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `user_id`, `subject`, `message`, `created_at`, `admin_reply`, `status`) VALUES
(1, 3, 'Prescription Help', 'hi i am facing iusse', '2025-12-25 05:54:43', 'i will look into your issue. Please give me some time to solve you issue . thankyou !', 'Resolved'),
(2, 5, 'Order Status', 'please guide me', '2025-12-25 10:43:57', 'it will delivery to you soon ', 'Resolved'),
(3, 6, 'Prescription Help', 'how to use it ', '2025-12-25 13:10:02', 'take it every for 1 week at day and night after your meal. Thankyou for choosing us !!', 'Resolved'),
(4, 3, 'Order Status', 'it will delivery soon ', '2025-12-25 15:05:46', 'yes you will ', 'Resolved');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int(11) NOT NULL,
  `m_name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `m_name`, `category`, `quantity`, `price`, `expiry_date`, `image_url`, `description`, `image`) VALUES
(1, 'Paracetamol  tablet', 'Tablet', 98, 140.00, '2026-12-31', NULL, 'Paracetamol is a widely used over-the-counter analgesic (pain reliever) and antipyretic (fever reducer) medication. It is known by different brand names globally, such as Tylenol and Panadol. ', '1766656664_paracetamol.jpeg'),
(2, 'Amoxicillin', 'Capsule', 88, 150.00, '2025-06-15', NULL, 'Amoxicillin is a widely used, prescription-only penicillin-class antibiotic used to treat a variety of bacterial infections, including those of the ears, nose, throat, urinary tract, and skin, by killing bacteria and preventing them from forming protective cell walls.', 'amoxicillin-drugs3.jpg'),
(4, 'NutriGrow Syrup', 'General', 2, 140.00, '2025-12-25', '', '	\r\nNutriGrow Syrup', '1766656552_nurti_grow_up_syrup.webp'),
(6, 'Azithromycin', 'Capsule', 73, 120.00, '2027-07-16', '', 'Is a prescription-only, broad-spectrum macrolide antibiotic used to treat various bacterial infections. It works by stopping the growth and multiplication of bacteria and is ineffective against viral infections like the common cold or flu. ', '1766656188_azithromycin.jpg'),
(7, 'Ciprofloxancin', 'Tablet', 60, 95.45, '2027-09-05', '', 'Ciprofloxacin has FDA approval to treat urinary tract infections, sexually transmitted infections (gonorrhea and chancroid), skin, bone, joint infections, prostatitis, typhoid fever, gastrointestinal infections, lower respiratory tract infections, anthrax, plague, and salmonellosis.', '1766656460_Ciprofloxacin.jpeg'),
(8, 'crocin', 'Tablet', 149, 40.00, '2027-02-23', '', 'Crocin Pain Relief Tablet is a painkiller used to treat headache. ', '1766656848_Crocin.jpeg'),
(9, 'ORS Powder', 'Tablet', 247, 30.00, '2026-02-02', '', 'Oral Rehydration Salt (ORS) powder is a combination of dextrose and essential electrolytes used to manage and prevent dehydration. It is typically a white or off-white powder sold in individual sachets designed to be dissolved in a specific amount of clean drinking water, usually 1 liter. ', '1766657957_ors_electrical_powder.jpg'),
(10, 'Digene Syrup', 'Syrup', 49, 140.00, '2026-10-15', '', 'Digene Syrup (also known as Digene Gel) is a popular over-the-counter antacid and anti-gas medication manufactured by Abbott Healthcare. It is available in various flavours, including Mint, Orange, and Mixed Fruit, and comes in different bottle sizes (e.g., 200 ml, 450 ml). ', '1766657895_Digene.jpeg'),
(11, 'Becosules', 'Capsule', 76, 125.00, '2027-05-21', '', 'The standard Becosules capsule is a hard gelatin capsule with a distinctive appearance and is a widely used multivitamin supplement (specifically B-complex and Vitamin C). ', '1766657822_Becosules.jpeg'),
(12, 'Paracetamol Syrup', 'Syrup', 50, 70.00, '2026-08-08', '', 'Paracetamol syrup, also known as acetaminophen, is a common over-the-counter liquid medication primarily used to relieve mild to moderate pain and reduce fever. It is particularly popular for use in children due to its liquid form and various flavorings, such as orange. ', '1766657996_paracetamol_syrup.jpeg'),
(13, 'Vitamin B12 Injection', 'Injection', 44, 150.00, '2026-01-14', '', 'Vitamin B12 deficiency anaemia is usually treated with injections of vitamin B12, called hydroxocobalamin', '1766658018_B12_Injection.jpeg'),
(14, 'Insulin Injection', 'Injection', 25, 450.00, '2026-03-25', '', 'An insulin injection delivers manufactured insulin via a syringe, pen, or pump to help people with diabetes manage blood sugar by moving sugar from the blood into cells for energy, acting as the insulin their body can\'t make or use properly, with different types affecting blood sugar at varying speeds, and injection sites (belly, thighs, arms, buttocks) needing rotation to prevent skin issues. ', '1766657932_insulin.jpeg'),
(15, 'Candid Antifungal', 'Tablet', 21, 140.55, '2026-12-12', NULL, 'Candid is a brand of antifungal medication containing the active ingredient clotrimazole, used to treat various fungal skin and yeast infections. It is available in several forms, including creams, dusting powders, gels, lotions, and mouth paints. ', '1767720806_candid.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `medicine_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('paid','cancelled','return_requested','returned') DEFAULT 'paid',
  `order_date` datetime DEFAULT current_timestamp(),
  `address` text DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `medicine_id`, `total_amount`, `status`, `order_date`, `address`, `pincode`, `phone`, `quantity`) VALUES
(2, 4, NULL, 140.00, '', '2025-12-25 00:34:47', NULL, NULL, NULL, NULL),
(4, 3, NULL, 280.00, 'cancelled', '2025-12-25 11:25:40', NULL, NULL, NULL, NULL),
(5, 5, 2, 150.00, 'cancelled', '2025-12-25 16:11:35', 'E/303 Nikkinagar socitey', '421301', '9664283308', 1),
(6, 5, 13, 300.00, 'returned', '2025-12-25 16:12:46', 'E/303 nikkinagar society', '421301', '9664283308', 2),
(7, 6, 8, 40.00, '', '2025-12-25 18:39:29', 'ghatkopar west', '400301', '9532654985', 1),
(8, 3, 1, 140.00, '', '2025-12-25 20:01:19', 'E/301 nikkinagar adharwadi jail road kalyan west ', '421301', '9664283308', 1),
(9, 3, 13, 300.00, 'cancelled', '2025-12-25 20:34:49', 'E/303 Nikki Nagar Adharwadi Jail Road , Kalayan west', '421301', '9321125930', 2),
(10, 5, 9, 90.00, '', '2025-12-25 20:56:46', 'E/303 kalyan west', '421302', '8454625665', 3),
(13, 3, 6, 120.00, '', '2026-01-11 08:25:28', NULL, NULL, NULL, 1),
(14, 3, 2, 150.00, '', '2026-01-11 08:25:40', NULL, NULL, NULL, 1),
(15, 3, 2, 150.00, '', '2026-01-11 08:25:53', NULL, NULL, NULL, 1),
(16, 3, 6, 240.00, '', '2026-01-11 08:32:56', 'E/301 nikkinagar adharwadi jail road kalyan west ', '421301', '9664283308', 2),
(17, 3, 15, 140.55, '', '2026-01-11 08:36:04', 'E/301  nikkinagar ', '421301', '9664283308', 1),
(18, 3, 11, 125.00, '', '2026-01-11 08:51:19', 'E/301 nikkinagar ', '421301', '9664283308', 1),
(19, 3, 10, 140.00, '', '2026-01-11 08:52:32', 'F/301 nikki nagar ', '421301', '+91 9321125930', 1),
(20, 3, 15, 252.99, '', '2026-01-11 08:53:05', 'E/301 nikkinagar', '421301', '9664283308', 2),
(21, 3, 1, 140.00, '', '2026-01-11 09:15:16', 'bjbjkjhb', '421301', '9664283308', 1),
(22, 3, 11, 125.00, '', '2026-01-11 09:18:07', 'fdjbhsguf', '421301', '9664283309', 1),
(23, 3, 15, 140.55, '', '2026-01-11 09:43:21', 'nnekhiew', '421301', '9664283308', 1),
(24, 3, 11, 125.00, '', '2026-01-11 09:45:40', 'hyyhthf', '421301', '9664283309', 1),
(25, 3, 11, 125.00, '', '2026-01-11 09:50:38', 'sdhewuif', '421301', '4546', 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `medicine_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price_at_purchase` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `medicine_id`, `patient_name`, `quantity_sold`, `sale_date`) VALUES
(1, 8, 'Neha ', 1, '2025-12-25 13:09:29'),
(2, 1, 'shubhangi khandait', 1, '2025-12-25 14:31:19'),
(3, 13, 'shubhangi khandait', 2, '2025-12-25 15:04:49'),
(4, 9, 'Ansh Bhosale', 3, '2025-12-25 15:26:46'),
(5, 13, 'shubhangi khandait', 1, '2026-01-05 05:02:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@test.com', 'admin123', '1', '2025-12-24 19:11:16'),
(2, 'madhura khandait', 'khandaitmadhura177@gmail.com', 'mm', '0', '2025-12-24 19:11:16'),
(3, 'shubhangi khandait', 'shubhangi@gmail.com', 'aa', '0', '2025-12-24 19:11:16'),
(5, 'Ansh Bhosale', 'ansh@gmail.com', 'ansh', '0', '2025-12-25 10:40:22'),
(6, 'Neha ', 'neha@gmail.com', 'neha', '0', '2025-12-25 13:08:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 04:10 AM
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
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`) VALUES
(3, 'Baby & Mother Care'),
(5, 'Hair Care'),
(4, 'Hygiene & Personal Care'),
(2, 'Oral Liquids/Syrups'),
(1, 'Tablets / Capsules');

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
(4, 3, 'Order Status', 'it will delivery soon ', '2025-12-25 15:05:46', 'yes you will ', 'Resolved'),
(5, 3, 'Product Info', 'when it wil l delivery ', '2026-01-28 04:28:20', 'ok\r\n', 'Resolved'),
(6, 3, 'Prescription', 'i mecine ', '2026-01-28 06:45:43', NULL, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int(11) NOT NULL,
  `m_name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT 'default.png',
  `quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `m_name`, `category`, `stock`, `price`, `expiry_date`, `image_url`, `description`, `image`, `quantity`) VALUES
(1, 'Paracetamol  tablet', 'Tablets / Capsules', 98, 140.00, '2026-12-31', NULL, 'Paracetamol is a widely used over-the-counter analgesic (pain reliever) and antipyretic (fever reducer) medication. It is known by different brand names globally, such as Tylenol and Panadol. ', 'MED_UPD_1769534002_1ea573cb.jpeg', 50),
(2, 'Amoxicillin', 'Tablets / Capsules', 88, 150.00, '2025-06-15', NULL, 'Amoxicillin is a widely used, prescription-only penicillin-class antibiotic used to treat a variety of bacterial infections, including those of the ears, nose, throat, urinary tract, and skin, by killing bacteria and preventing them from forming protective cell walls.', '1769781692_amoxicillin-drugs3.jpg.jpg', 49),
(4, 'NutriGrow Syrup', 'Tablets / Capsules', 2, 140.00, '2025-12-25', '', '	\r\nNutriGrow Syrup', 'MED_UPD_1769533988_e2ac1e09.webp', 50),
(6, 'Azithromycin', 'Tablets / Capsules', 73, 120.00, '2027-07-16', '', 'Is a prescription-only, broad-spectrum macrolide antibiotic used to treat various bacterial infections. It works by stopping the growth and multiplication of bacteria and is ineffective against viral infections like the common cold or flu. ', 'MED_UPD_1769533975_bb6c021e.jpg', 50),
(7, 'Ciprofloxancin', 'Tablets / Capsules', 59, 95.45, '2027-09-05', '', 'Ciprofloxacin has FDA approval to treat urinary tract infections, sexually transmitted infections (gonorrhea and chancroid), skin, bone, joint infections, prostatitis, typhoid fever, gastrointestinal infections, lower respiratory tract infections, anthrax, plague, and salmonellosis.', 'MED_UPD_1769533944_d2d5ea0a.jpeg', 50),
(8, 'crocin', 'Tablets / Capsules', 148, 40.00, '2027-02-23', '', 'Crocin Pain Relief Tablet is a painkiller used to treat headache. ', 'MED_UPD_1769533931_bc2c2790.jpeg', 50),
(9, 'ORS Powder', 'Tablets / Capsules', 244, 30.00, '2026-02-02', '', 'Oral Rehydration Salt (ORS) powder is a combination of dextrose and essential electrolytes used to manage and prevent dehydration. It is typically a white or off-white powder sold in individual sachets designed to be dissolved in a specific amount of clean drinking water, usually 1 liter. ', 'MED_UPD_1769533908_71a6d063.jpg', 50),
(10, 'Digene Syrup', 'Oral Liquids/Syrups', 48, 140.00, '2026-10-15', '', 'Digene Syrup (also known as Digene Gel) is a popular over-the-counter antacid and anti-gas medication manufactured by Abbott Healthcare. It is available in various flavours, including Mint, Orange, and Mixed Fruit, and comes in different bottle sizes (e.g., 200 ml, 450 ml). ', 'MED_UPD_1769533890_b3fc228c.jpeg', 50),
(11, 'Becosules', 'Tablets / Capsules', 73, 125.00, '2027-05-21', '', 'The standard Becosules capsule is a hard gelatin capsule with a distinctive appearance and is a widely used multivitamin supplement (specifically B-complex and Vitamin C). ', 'MED_UPD_1769533875_48ff0249.jpeg', 50),
(12, 'Paracetamol Syrup', 'Oral Liquids/Syrups', 50, 70.00, '2026-08-08', '', 'Paracetamol syrup, also known as acetaminophen, is a common over-the-counter liquid medication primarily used to relieve mild to moderate pain and reduce fever. It is particularly popular for use in children due to its liquid form and various flavorings, such as orange. ', 'MED_UPD_1769533863_882e9ca6.jpeg', 48),
(13, 'Vitamin B12 Injection', 'Tablets / Capsules', 44, 150.00, '2026-01-14', '', 'Vitamin B12 deficiency anaemia is usually treated with injections of vitamin B12, called hydroxocobalamin', 'MED_UPD_1769533842_4a69910c.jpeg', 50),
(14, 'Insulin Injection', 'Tablets / Capsules', 25, 450.00, '2026-03-25', '', 'An insulin injection delivers manufactured insulin via a syringe, pen, or pump to help people with diabetes manage blood sugar by moving sugar from the blood into cells for energy, acting as the insulin their body can\'t make or use properly, with different types affecting blood sugar at varying speeds, and injection sites (belly, thighs, arms, buttocks) needing rotation to prevent skin issues. ', 'MED_UPD_1769533830_730e08c9.jpeg', 50),
(15, 'Candid Antifungal', 'Tablets / Capsules', 20, 140.55, '2026-12-12', NULL, 'Candid is a brand of antifungal medication containing the active ingredient clotrimazole, used to treat various fungal skin and yeast infections. It is available in several forms, including creams, dusting powders, gels, lotions, and mouth paints. ', 'MED_UPD_1769533816_2a270392.jpeg', 50),
(16, 'Paracetamol 500mg', 'Tablets / Capsules', 99, 45.00, '2026-06-12', NULL, NULL, '1769782272_paracetamol.jpeg', 50),
(17, 'Lactogen PRO 1 ', 'Baby & Mother Care', 24, 450.00, '2026-05-03', NULL, NULL, '1769782446_infant formula.png', 50),
(18, 'Ascoril Syrups', 'Oral Liquids/Syrups', 38, 85.00, '2027-05-24', NULL, NULL, '1769782359_Ascoril.jpeg', 50),
(20, 'Benadryl ', 'Oral Liquids/Syrups', 20, 63.26, '2026-10-10', NULL, NULL, '1769782531_Benadryl.jpeg', 49),
(21, 'Corex Syrups', 'Oral Liquids/Syrups', 4, 101.22, '2026-03-03', NULL, NULL, '1769782598_Corex.jpeg', 50),
(22, 'Dubar Honitus Syrups', 'Oral Liquids/Syrups', 11, 120.02, '2026-06-07', NULL, NULL, '1769782642_Dabur Honitus.jpg', 50),
(23, 'Himalaya Koflet Syrups', 'Oral Liquids/Syrups', 9, 145.30, '2026-09-08', NULL, NULL, '1769782702_Himalaya Koflet.jpeg', 50),
(24, 'Indulekha Bhringa Hair Oil', 'Hair Care', 20, 170.05, '2026-06-12', NULL, NULL, '1769932252_Indulekha Bhringa Hair Oil.jpeg', 50),
(25, 'Neurobion Forte', 'Vitamins', NULL, 35.50, NULL, NULL, 'Vitamin B Complex for nerve health.', 'neurobion.png', 100),
(26, 'Amoxicillin 500mg', 'Antibiotics', NULL, 120.00, NULL, NULL, 'Broad-spectrum antibiotic for bacterial infections.', 'amoxicillin.png', 50),
(27, 'Dolo 650', 'Pain Relief', NULL, 30.00, NULL, NULL, 'Effective for fever and mild to moderate pain.', 'dolo.png', 200);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL DEFAULT 1,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `medicine_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `claimed_category` varchar(50) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT 'COD',
  `order_date` datetime DEFAULT current_timestamp(),
  `address` text DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `discount_reason` varchar(100) DEFAULT NULL,
  `discount_status` enum('none','pending','approved','rejected') DEFAULT 'none',
  `discounted_price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `discount_label` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `medicine_id`, `total_amount`, `status`, `payment_method`, `claimed_category`, `payment_mode`, `order_date`, `address`, `pincode`, `phone`, `quantity`, `discount_reason`, `discount_status`, `discounted_price`, `subtotal`, `discount_amount`, `discount_label`) VALUES
(2, 4, NULL, NULL, 140.00, '', NULL, NULL, 'COD', '2025-12-25 00:34:47', NULL, NULL, NULL, NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(4, 3, NULL, NULL, 280.00, 'cancelled', NULL, NULL, 'COD', '2025-12-25 11:25:40', NULL, NULL, NULL, NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(5, 5, NULL, 2, 150.00, 'cancelled', NULL, NULL, 'COD', '2025-12-25 16:11:35', 'E/303 Nikkinagar socitey', '421301', '9664283308', 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(6, 5, NULL, 13, 300.00, 'returned', NULL, NULL, 'COD', '2025-12-25 16:12:46', 'E/303 nikkinagar society', '421301', '9664283308', 2, NULL, 'none', NULL, 0.00, NULL, NULL),
(7, 6, NULL, 8, 40.00, '', NULL, NULL, 'COD', '2025-12-25 18:39:29', 'ghatkopar west', '400301', '9532654985', 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(8, 3, NULL, 1, 140.00, '', NULL, NULL, 'COD', '2025-12-25 20:01:19', 'E/301 nikkinagar adharwadi jail road kalyan west ', '421301', '9664283308', 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(9, 3, NULL, 13, 300.00, 'cancelled', NULL, NULL, 'COD', '2025-12-25 20:34:49', 'E/303 Nikki Nagar Adharwadi Jail Road , Kalayan west', '421301', '9321125930', 2, NULL, 'none', NULL, 0.00, NULL, NULL),
(10, 5, NULL, 9, 90.00, '', NULL, NULL, 'COD', '2025-12-25 20:56:46', 'E/303 kalyan west', '421302', '8454625665', 3, NULL, 'none', NULL, 0.00, NULL, NULL),
(13, 3, NULL, 6, 120.00, 'returned', NULL, NULL, 'COD', '2026-01-11 08:25:28', NULL, NULL, NULL, 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(14, 3, NULL, 2, 150.00, 'cancelled', NULL, NULL, 'COD', '2026-01-11 08:25:40', NULL, NULL, NULL, 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(15, 3, NULL, 2, 150.00, 'cancelled', NULL, NULL, 'COD', '2026-01-11 08:25:53', NULL, NULL, NULL, 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(16, 3, NULL, 6, 240.00, 'cancelled', NULL, NULL, 'COD', '2026-01-11 08:32:56', 'E/301 nikkinagar adharwadi jail road kalyan west ', '421301', '9664283308', 2, NULL, 'none', NULL, 0.00, NULL, NULL),
(17, 3, NULL, 15, 140.55, 'cancelled', NULL, NULL, 'COD', '2026-01-11 08:36:04', 'E/301  nikkinagar ', '421301', '9664283308', 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(18, 3, NULL, 11, 125.00, 'cancelled', NULL, NULL, 'COD', '2026-01-11 08:51:19', 'E/301 nikkinagar ', '421301', '9664283308', 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(19, 3, NULL, 10, 140.00, 'cancelled', NULL, NULL, 'COD', '2026-01-11 08:52:32', 'F/301 nikki nagar ', '421301', '+91 9321125930', 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(20, 3, NULL, 15, 252.99, 'cancelled', NULL, NULL, 'COD', '2026-01-11 08:53:05', 'E/301 nikkinagar', '421301', '9664283308', 2, NULL, 'none', NULL, 0.00, NULL, NULL),
(21, 3, NULL, 1, 140.00, 'cancelled', NULL, NULL, 'COD', '2026-01-11 09:15:16', 'bjbjkjhb', '421301', '9664283308', 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(22, 3, NULL, 11, 125.00, 'cancelled', NULL, NULL, 'COD', '2026-01-11 09:18:07', 'fdjbhsguf', '421301', '9664283309', 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(23, 3, NULL, 15, 140.55, 'cancelled', NULL, NULL, 'COD', '2026-01-11 09:43:21', 'nnekhiew', '421301', '9664283308', 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(24, 3, NULL, 11, 125.00, 'cancelled', NULL, NULL, 'COD', '2026-01-11 09:45:40', 'hyyhthf', '421301', '9664283309', 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(25, 3, NULL, 11, 125.00, 'cancelled', NULL, NULL, 'COD', '2026-01-11 09:50:38', 'sdhewuif', '421301', '4546', 1, NULL, 'none', NULL, 0.00, NULL, NULL),
(26, 3, 'shubhangi khandait', NULL, 265.55, '', 'Online', NULL, 'COD', '2026-01-27 22:03:17', 'E/301 Nikki Nagar Adharwadi Jail Road Kalyan West', '421301', '9664283308', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(27, 1, 'shubhangi khandait', NULL, 60.00, '', 'Online', NULL, 'COD', '2026-01-27 23:39:45', 'E/301 Nikki Nagar Adharwadi Jail Road Kalyan West ', '421301', '9664283308', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(28, 3, 'shubhangi khandait', NULL, 95.45, '', 'Online', NULL, 'COD', '2026-01-27 23:51:56', 'nikinagar', '421301', '9664283308', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(29, 3, 'shubhangi khandait', NULL, 24.00, '', 'Online', 'Senior Citizen', 'COD', '2026-01-28 12:05:59', 'E/301 Nikki Nagar Adharwadi jail Road Kalyan West ', '421301', '9664283308', NULL, NULL, 'none', NULL, 30.00, 6.00, NULL),
(30, 3, 'shubhangi khandait', NULL, 100.00, 'cancelled', 'Online', 'Senior Citizen', 'COD', '2026-01-28 12:14:22', 'e/301 Nikki ', '421301', '9664283308', NULL, NULL, 'none', NULL, 125.00, 25.00, NULL),
(31, 1, 'neha', NULL, 125.00, '', 'COD', 'General', 'COD', '2026-01-28 12:17:08', 'E nikk', '421301', '9664283308', NULL, NULL, 'none', NULL, 125.00, 0.00, NULL),
(32, 3, 'shubhangi khandait', NULL, 144.00, 'cancelled', 'Online', 'PH', 'COD', '2026-01-28 20:52:51', 'E/301 Nikkinagar Adharwadi jail ', '421301', '9664283308', NULL, NULL, 'none', NULL, 180.00, 36.00, NULL),
(33, 3, 'shubhangi khandait', NULL, 450.00, 'Processing', 'ONLINE', NULL, 'COD', '2026-01-29 22:39:00', 'E/301 Nikki Nagar Adharwadi Jail Road Kalayan West 421301', NULL, '9321125930', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(34, 7, 'shubhangi khandait', NULL, 221.24, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-03 12:46:46', 'E/301 ', NULL, '9664283308', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(35, 3, 'shubhangi khandait', NULL, 221.24, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-03 12:48:30', 'E/301 nikii', NULL, '9664283308', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(36, 3, 'Ankita Vispute', NULL, 221.24, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-03 12:50:51', 'E101 A wing', NULL, '9664283308', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(37, 8, 'Ankita Vispute', NULL, 145.30, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-03 12:53:12', 'E101', NULL, '9594565352', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(38, 8, 'Ankita Vispute', NULL, 101.22, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-03 12:54:36', 'hgjhe', NULL, '9664283308', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(39, 7, 'jh ', NULL, 101.22, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-03 13:02:02', 'hhh', NULL, '9833241409', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(40, 9, 'chikita', NULL, 85.00, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-03 19:14:16', 'E/01', NULL, '9664283309', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(41, 10, 'Taneem Khan', NULL, 130.00, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-03 19:19:37', 'kural west ', NULL, '7738690741', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(42, 7, 'chikita', NULL, 101.22, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-03 22:13:24', 'iuhihgj', NULL, '9664283308', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(43, 9, 'chikita', NULL, 120.02, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-03 22:18:57', 'wdsfs', NULL, '9664283308', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(44, 9, 'chikita', NULL, 145.30, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-03 22:43:22', 'E/301', NULL, '9664283308', NULL, NULL, 'none', NULL, 0.00, NULL, NULL),
(45, 9, 'chikita', NULL, 60.10, 'Processing', 'COD', NULL, 'COD', '2026-02-03 23:13:22', 'sdeff', NULL, '9664283308', NULL, NULL, 'none', NULL, 63.26, 3.16, 'New Customer'),
(46, 9, 'chikita', NULL, 126.00, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-05 08:24:27', 'E/301 nikki nGRA', NULL, '9664283308', NULL, NULL, 'none', NULL, 140.00, 14.00, 'Student/Staff'),
(47, 9, 'chikita', NULL, 0.00, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-05 08:25:22', 'E/301 nikki nGRA', NULL, '9664283308', NULL, NULL, 'none', NULL, 0.00, 0.00, 'Student/Staff'),
(48, 9, 'chikita', NULL, 0.00, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-05 08:26:10', 'E/301 nikki nGRA', NULL, '9664283308', NULL, NULL, 'none', NULL, 0.00, 0.00, 'Student/Staff'),
(49, 9, 'chikita', NULL, 135.00, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-05 08:28:49', 'E/301 nikkinagar ', NULL, '9664283308', NULL, NULL, 'none', NULL, 150.00, 15.00, 'Student/Staff'),
(50, 9, 'chikita', NULL, 60.10, 'Processing', 'ONLINE', NULL, 'COD', '2026-02-05 08:40:06', 'e/301 ', NULL, '9664283308', NULL, NULL, 'none', NULL, 63.26, 3.16, 'New Customer');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `medicine_name` varchar(255) DEFAULT NULL,
  `medicine_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `price_at_purchase` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `medicine_name`, `medicine_id`, `quantity`, `price`, `price_at_purchase`) VALUES
(1, 29, NULL, 9, 1, NULL, 30.00),
(2, 30, NULL, 11, 1, NULL, 125.00),
(3, 31, NULL, 11, 1, NULL, 125.00),
(4, 32, NULL, 8, 1, NULL, 40.00),
(5, 32, NULL, 10, 1, NULL, 140.00),
(6, 33, NULL, 17, 1, NULL, 450.00),
(7, 34, NULL, 22, 1, NULL, 120.02),
(8, 34, NULL, 21, 1, NULL, 101.22),
(9, 35, NULL, 22, 1, NULL, 120.02),
(10, 35, NULL, 21, 1, NULL, 101.22),
(11, 36, NULL, 22, 1, NULL, 120.02),
(12, 36, NULL, 21, 1, NULL, 101.22),
(13, 37, NULL, 23, 1, NULL, 145.30),
(14, 38, NULL, 21, 1, NULL, 101.22),
(15, 39, NULL, 21, 1, NULL, 101.22),
(16, 40, NULL, 18, 1, NULL, 85.00),
(17, 41, NULL, 18, 1, NULL, 85.00),
(18, 41, NULL, 16, 1, NULL, 45.00),
(19, 42, NULL, 21, 1, NULL, 101.22),
(20, 43, NULL, 22, 1, NULL, 120.02),
(21, 45, NULL, 20, 1, NULL, 63.26),
(22, 46, NULL, 12, 2, NULL, 70.00),
(23, 49, NULL, 2, 1, NULL, 150.00),
(24, 50, NULL, 20, 1, NULL, 63.26);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category`, `price`, `stock_quantity`, `image_path`) VALUES
(1, 'Vitamin B12 Complex', 'Tablets / Capsules', 15.00, 50, NULL),
(2, 'Infant Moisturizer', 'Baby & Mother Care', 12.50, 30, NULL),
(3, 'Antiseptic Liquid', 'Hygiene & Personal Care', 8.00, 100, NULL),
(4, 'Cough Suppressant', 'Oral Liquids/Syrups', 10.00, 45, NULL),
(5, 'Biotin Serum', 'Hair Care', 25.00, 20, NULL);

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
(5, 13, 'shubhangi khandait', 1, '2026-01-05 05:02:02'),
(6, 11, 'shubhangi khandait', 1, '2026-01-27 16:33:17'),
(7, 15, 'shubhangi khandait', 1, '2026-01-27 16:33:17'),
(8, 9, 'shubhangi khandait', 2, '2026-01-27 18:09:45'),
(9, 7, 'shubhangi khandait', 1, '2026-01-27 18:21:56');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `shop_name` varchar(255) DEFAULT 'MIMS PHARMACY',
  `currency` varchar(10) DEFAULT 'INR',
  `timezone` varchar(100) DEFAULT 'Asia/Kolkata'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `shop_name`, `currency`, `timezone`) VALUES
(1, 'MIMS PHARMACY - HQ', 'INR', 'Asia/Kolkata');

-- --------------------------------------------------------

--
-- Table structure for table `support_messages`
--

CREATE TABLE `support_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `admin_reply` text DEFAULT NULL,
  `status` enum('pending','solved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_messages`
--

INSERT INTO `support_messages` (`id`, `user_id`, `order_id`, `message`, `admin_reply`, `status`, `created_at`) VALUES
(1, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:21'),
(2, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:23'),
(3, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:24'),
(4, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:25'),
(5, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:29'),
(6, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:30'),
(7, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:30'),
(8, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:30'),
(9, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:30'),
(10, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:30'),
(11, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:30'),
(12, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:31'),
(13, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:31'),
(14, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:31'),
(15, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:31'),
(16, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:31'),
(17, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:31'),
(18, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:32'),
(19, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:32'),
(20, 9, NULL, 'hi', NULL, 'pending', '2026-02-03 14:23:32'),
(21, 9, NULL, 'i am not reciecing u', 'ok\r\n', 'solved', '2026-02-03 16:20:31'),
(22, 7, NULL, 'hi', 'hi this is ur customer at your service ', 'solved', '2026-02-03 16:28:20');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `theme` varchar(20) DEFAULT 'dark'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `theme`) VALUES
(1, 'admin', 'admin@test.com', 'admin123', '1', '2025-12-24 19:11:16', 'dark'),
(2, 'madhura khandait', 'khandaitmadhura177@gmail.com', 'mm', '0', '2025-12-24 19:11:16', 'dark'),
(3, 'shubhangi khandait', 'shubhangi@gmail.com', 'password123', '0', '2025-12-24 19:11:16', 'dark'),
(5, 'Ansh Bhosale', 'ansh@gmail.com', 'ansh', '0', '2025-12-25 10:40:22', 'dark'),
(6, 'Neha ', 'neha@gmail.com', 'neha', '0', '2025-12-25 13:08:05', 'dark'),
(7, 'admin2', 'khandaitmadhura177@gmail.com', 'Madhura@chiki7436', '1', '2026-01-29 14:34:13', 'dark'),
(8, 'Ankita Vispute', 'visputeankita19@gmail.com', 'aa', '0', '2026-02-03 07:22:24', 'dark'),
(9, 'chikita', 'chikita5467@gmail.com', 'aa', '0', '2026-02-03 13:43:21', 'dark'),
(10, 'Tasneem Khan', 'khantasneem511@gmail.com', 'kk', '0', '2026-02-03 13:48:13', 'dark');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

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
-- Indexes for table `messages`
--
ALTER TABLE `messages`
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
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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

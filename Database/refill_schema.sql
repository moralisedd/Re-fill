SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `refill`
--

-- ============================================================
-- TABLE: cafes
-- Participating independent cafes in the Re-fill network.
-- ============================================================

CREATE TABLE `cafes` (
  `cafe_id`    int(10) UNSIGNED NOT NULL,
  `name`       varchar(100)     NOT NULL,
  `address`    varchar(255)     NOT NULL,
  `city`       varchar(100)     NOT NULL,
  `postcode`   varchar(10)      NOT NULL,
  `phone`      varchar(20)               DEFAULT NULL,
  `email`      varchar(255)              DEFAULT NULL,
  `logo_url`   varchar(500)              DEFAULT NULL,
  `is_active`  tinyint(1)       NOT NULL DEFAULT 1,
  `created_at` datetime         NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cafes`
--

INSERT INTO `cafes` (`cafe_id`, `name`, `address`, `city`, `postcode`, `phone`, `email`, `logo_url`, `is_active`, `created_at`) VALUES
(1, 'The Daily Grind',  '12 Division Street',  'Sheffield', 'S1 4GF',  '0114 201 0001', 'hello@dailygrind.co.uk',     NULL, 1, '2026-04-21 09:46:17'),
(2, 'Brew & Bloom',     '45 Ecclesall Road',   'Sheffield', 'S11 8PU', '0114 201 0002', 'info@brewandbloom.co.uk',    NULL, 1, '2026-04-21 09:46:17'),
(3, 'Common Ground',    '7 Sharrow Vale Road', 'Sheffield', 'S11 8ZL', '0114 201 0003', 'hi@commonground.cafe',       NULL, 1, '2026-04-21 09:46:17');

-- ============================================================
-- TABLE: cafe_staff
-- Staff accounts linked to a specific cafe.
-- Owners can manage rewards; baristas can only scan/validate.
-- ============================================================

CREATE TABLE `cafe_staff` (
  `staff_id`      int(10) UNSIGNED              NOT NULL,
  `cafe_id`       int(10) UNSIGNED              NOT NULL,
  `email`         varchar(255)                  NOT NULL,
  `password_hash` varchar(255)                  NOT NULL,
  `full_name`     varchar(100)                  NOT NULL,
  `role`          enum('owner','barista')        NOT NULL DEFAULT 'barista',
  `is_active`     tinyint(1)                    NOT NULL DEFAULT 1,
  `created_at`    datetime                      NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cafe_staff`
--

INSERT INTO `cafe_staff` (`staff_id`, `cafe_id`, `email`, `password_hash`, `full_name`, `role`, `is_active`, `created_at`) VALUES
(1, 1, 'owner@dailygrind.co.uk',   '$2y$12$JeQ/CXIOwyX6iotG1LPDleeA6X1ie0oRuytgjYld77JElTidQn.Qu', 'Alice Thornton', 'owner',   1, '2026-04-21 09:46:17'),
(2, 1, 'barista@dailygrind.co.uk', '$2y$12$JeQ/CXIOwyX6iotG1LPDleeA6X1ie0oRuytgjYld77JElTidQn.Qu', 'Sam Patel',      'barista', 1, '2026-04-21 09:46:17');

-- ============================================================
-- TABLE: qr_tokens
-- Dynamic, short-lived tokens presented by the customer.
-- Inspired by Alipay CPM (Customer-Presented Mode):
--   - Token expires after 60 seconds (expires_at)
--   - Nonce ensures one-time use even if token is screenshotted
--   - is_used flag prevents replay attacks
-- ============================================================

CREATE TABLE `qr_tokens` (
  `token_id`    int(10) UNSIGNED NOT NULL,
  `user_id`     int(10) UNSIGNED NOT NULL,
  `token_value` varchar(128)     NOT NULL,  -- cryptographically random (bin2hex)
  `nonce`       varchar(64)      NOT NULL,  -- separate one-time-use nonce
  `expires_at`  datetime         NOT NULL,  -- created_at + 60 seconds
  `is_used`     tinyint(1)       NOT NULL DEFAULT 0,
  `created_at`  datetime         NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qr_tokens`
--

INSERT INTO `qr_tokens` (`token_id`, `user_id`, `token_value`, `nonce`, `expires_at`, `is_used`, `created_at`) VALUES
(10, 1, '42416ea436df53e2c1d2d3fe68a900fa89d2bdff44cbbc5317bb7c9e25b591d7', '27dfbfc2313557b9469768b809ede8bd', '2026-04-21 11:01:17', 1, '2026-04-21 10:00:17'),
(11, 1, '091a89062f623f0c8f2e0fb987f0a5b27811bd257b5edfa612a305ad5b66391d', '97a42fb555d7422dba7f9c1d9da29ea9', '2026-04-21 11:02:19', 1, '2026-04-21 10:01:19'),
(12, 1, '9bb9140b5ff70fabc42930d1be1a295dc6fda4d8acd75ff56cad232460697820', 'eb75adf0edc26df7d75edf7c7248192e', '2026-04-21 11:02:21', 1, '2026-04-21 10:01:21'),
(13, 1, 'c27aaead846265df08bfa36600e83b014db0dc0cbd80ca3ccf4184cc91e3556d', '4102664b29d9d6acb2abba078697ae18', '2026-04-21 11:02:26', 1, '2026-04-21 10:01:26'),
(14, 1, '6379e8eb749a6216a33671a7db555eb644cd130cbb4df25f5e34a7eee117b01d', 'de80d76eec4327b99a0fdf893d4b4da1', '2026-04-21 11:02:28', 1, '2026-04-21 10:01:28'),
(15, 1, 'a57568b29e79b957b690bc311664c16fae4a276802d1d8fe828758ce72c07078', '9bb88d9dbc161f3a4cd68a94822327dd', '2026-04-21 11:02:30', 1, '2026-04-21 10:01:30'),
(18, 1, 'b25857a9fe8e04c064815daf506a0089282c16754d2fe317612567604fc30127', '342ad7e0fad883066014f3dbfb209903', '2026-04-21 11:12:31', 1, '2026-04-21 10:11:31'),
(19, 1, '7033d03d05a9cee1133ca013e15f92dd93e062b732a866b202c5270f4a76c503', '4d10adf7e32a250646f74fa75c9d3d55', '2026-04-21 11:20:00', 1, '2026-04-21 10:19:00'),
(20, 1, 'ce1e24f3f8b01e51ff0a927ad998316ff8b164eb0890a8533405ded3170a37b5', 'cf2fef41d8df3bea208829900dd36596', '2026-04-21 11:20:03', 1, '2026-04-21 10:19:03'),
(21, 1, 'deca120785de0063769c567592c82cb2a7e84954e3849e2033ca74fd362d4617', '2238a9ca756ce40cfca38f7ef9e3e4d2', '2026-04-21 11:20:04', 1, '2026-04-21 10:19:04'),
(22, 1, '9b08e40edaca7450a33fabd11ad49336e583a0827b0fe0886de68a836cc1657d', 'a4ae71895dacbc37f2194ae673b9b85d', '2026-04-21 11:20:08', 1, '2026-04-21 10:19:08'),
(23, 1, 'fc75094e62385ffa17632ffa6102cbec08cae9851dab0e822034ec59d205ac4e', 'f9eff43414c87647b4c84d87217f2edb', '2026-04-21 11:20:30', 1, '2026-04-21 10:19:30'),
(24, 1, 'fd38e536eb31a823a2e1125cfe90bbaf0ef01f096eb0c6e86cca987033487240', '255e943369579269f2e0121b9fdff094', '2026-04-21 11:30:55', 1, '2026-04-21 10:29:55'),
(25, 1, 'c1034bba2bc8e7c6fc98e459c13b5cd0aec9d22cf667c6cb7c47f52f7dcc30bc', '1a72fe4a1b0d805e78451fde1bed596e', '2026-04-21 11:35:42', 1, '2026-04-21 10:34:42'),
(26, 1, 'c7cf8623a5d347cec64c6de8a9cf4e3aeafee7533a537951754b47a6d3cb6981', '7091ad21bf6b0f238d4dc7a9151b6cc0', '2026-04-21 11:36:26', 1, '2026-04-21 10:35:26'),
(27, 1, '4710045c89e5c23fa1c7961aed4c56870b1d32de0345b480ddb19d3031c0ddd8', 'e412834cb1a1f4d0df6934c46591821f', '2026-04-21 11:36:27', 0, '2026-04-21 10:35:27'),
(28, 1, '5536db747bbf4783d17b43eba6715303ac392d14d1273b19cfccd9f0d7b379bb', 'bed79adf161067b248827c7197d804f0', '2026-04-21 18:46:28', 1, '2026-04-21 17:45:28'),
(29, 1, '5e23df4c3d917f842b1197c04f4dbd01ace940af2c0a7b462cc4c7934c1c5aca', '4974ccff78fc6da99865eb6ef4a131fe', '2026-04-21 18:46:34', 1, '2026-04-21 17:45:34'),
(30, 1, '7a500cdb5948e47c6f2227acded6396d65c5b0227f3116b2b6e616899918dc52', 'f9502e5dafd47a765c8b1533271ff0b8', '2026-04-21 18:46:35', 1, '2026-04-21 17:45:35'),
(31, 1, 'f8178fc8bfa3fd3b0a68c081c2fd7930c00541297106cdeed36b08dafe9c585a', 'c3470826b0b0a410a58142236fc043fd', '2026-04-21 18:46:36', 1, '2026-04-21 17:45:36'),
(32, 1, '9726abc0ee6c579ef4c89105afb9d3ef885aeec5ae3c8918e3511158dd5cef71', 'bb7a0f3fd50123708a74901248e12233', '2026-04-21 18:46:37', 1, '2026-04-21 17:45:37'),
(33, 1, 'ae608c2bfa7132cafa404f4086f9b72bcdf5a56d35933facee6e5c454894d9b6', '252962fcf7103c9318f47821f811bf99', '2026-04-21 18:46:38', 1, '2026-04-21 17:45:38'),
(34, 1, '338c091227a2d50a72515240c737d4b7c1a03985645227a4843e53f315f1e314', '282979eeec585ee14c35ad25a4a5900d', '2026-04-21 18:47:42', 1, '2026-04-21 17:46:42'),
(35, 1, '75ae7ce35c299938650a30bed9375d37cef97273e87baf013bd3fc17285eb8ae', '1b9e5d8a9a29893fe73283f4f510ca34', '2026-04-21 18:48:40', 1, '2026-04-21 17:47:40'),
(36, 1, '43558591f5585b6f444408038918f955e9045e5521deadd22c28ff7124399890', '6d305c9af14c9ad67016e6b329b9d06c', '2026-04-21 18:54:55', 1, '2026-04-21 17:53:55'),
(37, 1, '0c43c723d46bc13c4e2f46841dac22899c622322fe9d1bb56d653d986a1a0650', '82e8578eb5c5ef6fa99c81191ca66cc6', '2026-04-21 18:55:08', 1, '2026-04-21 17:54:08'),
(38, 1, 'f136c250ee451da3d5b75741c726abdc3d2a807731f03aa0965a6877c5cef622', 'b8b9fadc6e26c5b6b5d9d1f2613a135d', '2026-04-21 18:55:11', 1, '2026-04-21 17:54:11'),
(39, 1, '7243d5c70c72db3fbbf612f9696d7dd23f3873808888b353121917cc680f526d', '4307f378d31208dc1530c20edac47034', '2026-04-21 18:55:13', 1, '2026-04-21 17:54:13'),
(40, 1, 'c0235cc6624642b5737bef6a93d425ab51ff5627368edc98bd8544563349d965', 'd69b235cfe403c94892d8532da94be6a', '2026-04-21 18:55:15', 1, '2026-04-21 17:54:15'),
(41, 1, 'a295362c7ee06385e08c4193f4dff321676c0195284affd75e9e2bbef9302f47', '66c4e83b7c5191ee5070000f95ff6221', '2026-04-21 18:55:15', 1, '2026-04-21 17:54:15'),
(42, 1, 'cd2b7977a455903e1a21d3eb4350287330347eac250d54932628143527478f9b', '9c31e3809b25838e6b54adeba2d1fc2f', '2026-04-21 18:55:17', 1, '2026-04-21 17:54:17'),
(43, 1, '65f4239605c9cad61011a3c84f5d6a3d9b2f8f2d5edc9425d2d5b52c7f1a4ebf', 'fcb235fe063712745b8213232cf9eae8', '2026-04-21 18:56:02', 1, '2026-04-21 17:55:02'),
(44, 1, 'bedc6800a46975f3498105360a3763b9f24991660a0a04355472d6d5e91c827c', 'be3ba19d922fd364077c868518b008a0', '2026-04-21 18:56:17', 1, '2026-04-21 17:55:17'),
(45, 1, 'e70ebb6dad43ddb0b9accb59a1d5408e9ed4c5bbb8bece648ab5cc366ae76d47', '2ebae9fd084ac12113c8811995307942', '2026-04-21 18:56:19', 1, '2026-04-21 17:55:19'),
(46, 1, 'e2fc4a080293b115b70fc2887c10a573bc74bd92c030be8aac963ccd71c0ba55', '529ae05f439075ff1a39d750a1a85859', '2026-04-21 18:56:22', 1, '2026-04-21 17:55:22'),
(47, 1, '908f7517002cb5c73f57faf4c8e51f0205472f70013ee458b3a64b084045441b', 'b3d4f097ed1dd1619173983fc9dce269', '2026-04-21 19:02:43', 1, '2026-04-21 18:01:43'),
(48, 1, '93e4139c3a18b0a5160d0ada8edbd79c36fa2cb0540defd2eb850cf3e833ad44', '67540659165fecb1d11a22276121dd86', '2026-04-21 19:12:38', 1, '2026-04-21 18:11:38'),
(49, 1, '2674b35279482b40a00aeb40618f86e7e0e4b36f7630f7c4d139cea7f0eaa716', '610c7b3232cf1a92e36ff6fd5fc0b55e', '2026-04-21 19:12:48', 1, '2026-04-21 18:11:48'),
(50, 1, 'b45e5e33292cc2762e4d6df0740dc9e9a56537f56f9b13a9ffccda788ff95406', '937edec4c73068f9894977a9fa860d81', '2026-04-21 19:13:44', 1, '2026-04-21 18:12:44'),
(51, 1, '9ecd33047577aa00e54fc2e2a7ba723a6dd6e3ffa1f6744d0a1ad34fef498d1b', '3ede4386c7cd9dbe3ff9a0cf06dd0860', '2026-04-21 19:13:46', 1, '2026-04-21 18:12:46'),
(52, 1, '7335dba368acba5fe090abbff677718ccfa7686886bc1181615a75a124491a24', '042840b5de42ca8b9327f45f6f9bc5da', '2026-04-21 19:17:20', 1, '2026-04-21 18:16:20'),
(53, 1, '9f3845e6e60e05625a8f2690c5c8af22f32f4d1e8d7cd1f23dad4dc39cc4c103', '30cd454404c4d36afda246516e905e0b', '2026-04-21 19:19:51', 1, '2026-04-21 18:18:51'),
(54, 1, '4a66329700165b2993ec56dd5a7a8f74030b6bf7542b5b1212381f2ef2badd8a', 'c6b3273bf681e1678d6c98584082e8df', '2026-04-21 19:19:53', 1, '2026-04-21 18:18:53'),
(55, 1, '3fab6bf8c02bd57f2386b93db3bc9facfa6199c6e12bede795be8060e795d6ac', '813516dc8c33599657b4d4c446bd6d02', '2026-04-21 19:19:54', 1, '2026-04-21 18:18:54'),
(56, 1, '8e8306cca6818aa0b9cb47a016d4ddbfb0795bc38ae74b821a443fb00d12b938', 'cc5a06b9ab0e27daddc818bad620ea84', '2026-04-21 19:19:55', 1, '2026-04-21 18:18:55'),
(57, 1, '15160a0a312bcc3ab9bf25244bd494bb6a6361a38c8a3813bfa3febcae085ec4', '50b3b1d1bb29ba354b04ae7b9850aebb', '2026-04-21 19:23:36', 1, '2026-04-21 18:22:36'),
(58, 1, '7d5c0b22b93113b6124443ec10401a07c9de66d92c620a7315a543645945d0fd', 'e8aeef343f5b83439e3b6db9962729ca', '2026-04-21 19:23:41', 1, '2026-04-21 18:22:41'),
(59, 1, 'cef0060c4adde65378dc539dbaf0968a8fa1b88e70ff2aea379fb8b9b17cb09e', 'f1237c1b815deb19f20fdcb1e85c89ee', '2026-04-21 19:24:14', 1, '2026-04-21 18:23:14'),
(60, 1, '75c29f1e7f33a773b65cb0082b3af2db45e8f6f48c0767ca23a6eedb714dfdbe', 'c9a2d135453b262714c28fe33ccde749', '2026-04-21 19:28:57', 1, '2026-04-21 18:27:57'),
(61, 1, 'e2de1b2e5c87bec40aa1352bf3372b8c994df0a89eee7e763d198dce8475348e', 'b41f1a775032efe1e183f0879c56d9f3', '2026-04-21 19:29:00', 1, '2026-04-21 18:28:00'),
(62, 1, '31d4de504ded07d343d79a2df269b73d06a92272aa26602e76a367f8e034312b', 'aea92fa611d00ca4249eafd4bcdeec7f', '2026-04-21 19:29:01', 0, '2026-04-21 18:28:01'),
(63, 1, '4403f99eee758c479c5c53592a3b4294716be993d7b8fabeb050771a0d6abbb7', 'c817eee605a1de66b49b76729594ce55', '2026-04-21 23:54:51', 1, '2026-04-21 22:53:51'),
(64, 1, '7ad71c529cffcdce4edb3cf4a44e1c7c0c6f4754bdf55809676b625faac2c3a1', '92b1b99edd89b614f7f5808681335c6f', '2026-04-21 23:54:54', 1, '2026-04-21 22:53:54'),
(65, 1, '91a49164f27763d964788b406eb2defbb62f9e5ad5136e359d916d7e6099750b', '8f96f76d06549671e6954398bef8dc56', '2026-04-21 23:54:55', 1, '2026-04-21 22:53:55'),
(66, 1, '8484a36a0a5bc31c8f4d898bd8b828468bdcae3a155c2e795c3dabe61206aa16', 'a85f96e4db174061fe26f2b1f69c26d2', '2026-04-21 23:54:55', 1, '2026-04-21 22:53:55'),
(67, 1, '831c4900b048acbca788a5d8a3cd7264cd001d220f160ce432799ba15bd3c0aa', '1e177af7056561d9dd3db223f2f8f779', '2026-04-21 23:54:56', 1, '2026-04-21 22:53:56'),
(68, 1, 'ccc371a041c65b23bf1b089fdf1fc08b4a0aeb4af77fe55eed6ba82dce5015f0', '6a916f899ba04e05cf65a872a8eb5322', '2026-04-21 23:54:57', 1, '2026-04-21 22:53:57'),
(69, 1, '5c628a08c2a033b7eb5b6ef44d731daafe01edba76a7295645252106618ff673', '8a793ce4c0c309f266825a0f34021090', '2026-04-21 23:54:57', 1, '2026-04-21 22:53:57'),
(70, 1, '4ee458dd0a90fc486163dedf06947ca8cf6c0f0ef09f1a37269d3ef9c4b060b5', 'fa25fa08553bf157387b3378d8ea3955', '2026-04-21 23:54:58', 1, '2026-04-21 22:53:58'),
(71, 1, 'e5e48e26b34176562c9b6cc4f53f4a4e70815f59e6b6a5939533e6a53d472faf', '490cbbb676b920738f11d2dde9b367fc', '2026-04-21 23:54:59', 1, '2026-04-21 22:53:59'),
(72, 1, '5c1df0d74f255e12255bf916dd2fa90534734dba990bb8a2cc7ab9e24d1e52c6', 'dd8b3407cd71607fcb1e175f91c19cf0', '2026-04-21 23:54:59', 1, '2026-04-21 22:53:59'),
(73, 1, '25f75a57f5f79e4017ea571cf98154dd0e15281c75d41bc2c1bb5482adbed057', 'd21c2859510f5775414b53f8d1fcf329', '2026-04-21 23:55:00', 1, '2026-04-21 22:54:00'),
(74, 1, 'ac34c172c6193bf16075e4c7aadee97729959bd63fe9f1dd853f3e908de47bf6', 'b67b8958409e2ce6f244cf43821a9066', '2026-04-21 23:55:01', 1, '2026-04-21 22:54:01'),
(75, 1, '52009e885d497a5a6757fa9d9c0a3c9e4cd6f81b3126dd1ec3000d46c66b7c3f', '82c0f664f174d9fca43c2f3e573f67d3', '2026-04-21 23:55:02', 1, '2026-04-21 22:54:02'),
(76, 1, 'a38828aecca5b42247d9bb382cfef473f87ace5b5dda222107567460eab44ac7', '0cc75ba14be7cd3ad4732352edfff751', '2026-04-21 23:55:03', 1, '2026-04-21 22:54:03'),
(77, 1, '5377ff5029ea4fab123ac94582c6a1947d502536163c0e00b322b6d56c6a75eb', '49ea668bf51a94c81e505829c9d51de0', '2026-04-21 23:55:03', 1, '2026-04-21 22:54:03'),
(78, 1, 'b04a55a4895bebdb835f43e0467a78f1e7fad3bbbffab93953e8d2bd4fe2305a', 'd037c0fdfc7dc7b08cd2483d9984d399', '2026-04-22 00:06:37', 1, '2026-04-21 23:05:37'),
(79, 1, '2192cd5a77a304b961ce1131aaa91133a6caf3e7a5e86f20d7a00cc41b8424b6', '9914c43f8eff455c4f4763175590d825', '2026-04-22 00:06:40', 1, '2026-04-21 23:05:40'),
(80, 1, '80df60a398d8d51116d177b4b7af1daff02914aa0bf58b99e912f2a547d18bf1', 'c2f6d7c8a141059a35e2514d126b8e16', '2026-04-22 00:06:44', 1, '2026-04-21 23:05:44'),
(81, 1, 'afa0962a08b24ae35760414594f2fe50dc1a1ede4b34d704fdd633ab4987dbc3', 'a63cbe37141ab486954ea049392d271a', '2026-04-22 00:06:49', 1, '2026-04-21 23:05:49'),
(82, 1, 'c3085a6210dc7ca41f1be85fc6fd7eff3e335fc5d6a6de58ca95e14efcf382cc', '5e8509de7493838eadae3c98ea7d1704', '2026-04-22 00:16:52', 1, '2026-04-21 23:15:52'),
(83, 1, '0415d7a19c66c4cebd850397e26745e273ac2f2fef8c897349c100c3359e7ad2', 'c577109eb6f371f893a009b35f889e98', '2026-04-22 00:17:45', 1, '2026-04-21 23:16:45'),
(84, 1, '39f72ab571216e8639f180709774a6e58928220a6d1143bbf7f75b8e9129774a', 'fef6003ba8a8b8b07d99bda231d9f04d', '2026-04-22 00:18:17', 1, '2026-04-21 23:17:17'),
(85, 1, '31113cfe9a1ec2122f1d7910aee1a06224aa57bc46a6b5a32e04ef3caa009c3d', 'af1a87a1d879aa43619bc347b182b511', '2026-04-22 00:25:39', 1, '2026-04-21 23:24:39'),
(86, 1, '1be3fda83b2a8ccf349de9fbde86cad549573e4be6be420619cc7bf09d207ca0', 'a13c1896ff7df01b9fab8e986f196b63', '2026-04-22 00:26:36', 1, '2026-04-21 23:25:36'),
(87, 1, '8eff9d2dd2b32136bb710ef4ed2c2bf5654ad54e9e30c123d7b2e9121e9f997b', 'd724699982e4abed78b3950e3b1dfab6', '2026-04-22 00:27:19', 1, '2026-04-21 23:26:19'),
(88, 1, 'cbc0c35c19be2451f15a342515dea0061d688570e7cc471406b0549e38575a8b', 'c88ecd57a20affd03f3abe7e65cb4294', '2026-04-22 00:28:02', 1, '2026-04-21 23:27:02');

-- ============================================================
-- TABLE: rewards
-- Reward tiers that users can redeem points against.
-- Managed by cafe owners or a platform admin.
-- ============================================================

CREATE TABLE `rewards` (
  `reward_id`       int(10) UNSIGNED NOT NULL,
  `name`            varchar(100)     NOT NULL,
  `description`     text                      DEFAULT NULL,
  `points_required` int(10) UNSIGNED NOT NULL,
  `is_active`       tinyint(1)       NOT NULL DEFAULT 1,
  `created_at`      datetime         NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`reward_id`, `name`, `description`, `points_required`, `is_active`, `created_at`) VALUES
(1, 'Free Hot Drink',     'Any hot drink of your choice, on us.',              10, 1, '2026-04-21 09:46:17'),
(2, 'Free Cold Drink',    'Any cold drink — smoothie, juice, or iced coffee.', 10, 1, '2026-04-21 09:46:17'),
(3, '50p Off Any Drink',  'Half off your next cup.',                            5, 1, '2026-04-21 09:46:17'),
(4, 'Free Slice of Cake', 'Treat yourself — any cake from the counter.',        15, 1, '2026-04-21 09:46:17'),
(5, 'Loyalty Badge',      'Digital badge shown on your Re-fill profile.',        1, 1, '2026-04-21 09:46:17');

-- ============================================================
-- TABLE: sessions (PHP session store)
-- Keeps authenticated session state server-side for security.
-- ============================================================

CREATE TABLE `sessions` (
  `session_id`    varchar(128) NOT NULL,
  `user_id`       int(10) UNSIGNED         DEFAULT NULL,
  `staff_id`      int(10) UNSIGNED         DEFAULT NULL,
  `ip_address`    varchar(45)              DEFAULT NULL,
  `user_agent`    varchar(255)             DEFAULT NULL,
  `payload`       text         NOT NULL,              -- serialised session data
  `last_activity` datetime     NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: transactions
-- Immutable audit log of every point-earn or redemption event.
-- Records who awarded points, at which cafe, using which token.
-- ============================================================

CREATE TABLE `transactions` (
  `transaction_id`   int(10) UNSIGNED          NOT NULL,
  `user_id`          int(10) UNSIGNED          NOT NULL,
  `cafe_id`          int(10) UNSIGNED          NOT NULL,
  `staff_id`         int(10) UNSIGNED          NOT NULL,
  `token_id`         int(10) UNSIGNED                   DEFAULT NULL,  -- NULL for redemptions
  `reward_id`        int(10) UNSIGNED                   DEFAULT NULL,  -- NULL for earn events
  `transaction_type` enum('earn','redeem')     NOT NULL,
  `points_delta`     int(11)                   NOT NULL,               -- positive = earn, negative = redeem
  `created_at`       datetime                  NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `cafe_id`, `staff_id`, `token_id`, `reward_id`, `transaction_type`, `points_delta`, `created_at`) VALUES
(1, 1, 1, 2, 59, NULL, 'earn', 1, '2026-04-21 18:23:19'),
(2, 1, 1, 2, 83, NULL, 'earn', 1, '2026-04-21 23:16:53'),
(3, 1, 1, 2, 86, NULL, 'earn', 1, '2026-04-21 23:25:49'),
(4, 1, 1, 2, 88, NULL, 'earn', 1, '2026-04-21 23:27:16');

-- ============================================================
-- TABLE: users
-- Customers who participate in the loyalty programme.
-- ============================================================

CREATE TABLE `users` (
  `user_id`        int(10) UNSIGNED NOT NULL,
  `email`          varchar(255)     NOT NULL,
  `password_hash`  varchar(255)     NOT NULL,  -- bcrypt hash (cost ≥12)
  `full_name`      varchar(100)     NOT NULL,
  `phone`          varchar(20)               DEFAULT NULL,
  `points_balance` int(10) UNSIGNED NOT NULL  DEFAULT 0,
  `is_active`      tinyint(1)       NOT NULL  DEFAULT 1,
  `created_at`     datetime         NOT NULL  DEFAULT current_timestamp(),
  `updated_at`     datetime         NOT NULL  DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `full_name`, `phone`, `points_balance`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'demo@refill.app', '$2y$12$MV9sbbIASJXhQxQenRH2FOUbKs3TeB8Zpq7CLa.Hvr3BkU4DO04sm', 'Demo Customer', '07700 900000', 4, 1, '2026-04-21 09:46:17', '2026-04-21 23:27:16');

-- ============================================================
-- VIEWS (for reporting and video demo)
-- ============================================================

--
-- Stand-in structure for view `vw_cafe_activity`
-- (See below for the actual view)
--
CREATE TABLE `vw_cafe_activity` (
`cafe_id` int(10) unsigned
,`cafe_name` varchar(100)
,`unique_customers` bigint(21)
,`total_transactions` bigint(21)
,`total_points_awarded` decimal(32,0)
,`total_points_redeemed` decimal(32,0)
);

--
-- Stand-in structure for view `vw_leaderboard`
-- (See below for the actual view)
--
CREATE TABLE `vw_leaderboard` (
`user_id` int(10) unsigned
,`full_name` varchar(100)
,`points_balance` int(10) unsigned
,`total_visits` bigint(21)
,`rank_position` bigint(21)
);

--
-- Stand-in structure for view `vw_user_history`
-- (See below for the actual view)
--
CREATE TABLE `vw_user_history` (
`transaction_id` int(10) unsigned
,`customer_name` varchar(100)
,`customer_email` varchar(255)
,`cafe_name` varchar(100)
,`transaction_type` enum('earn','redeem')
,`points_delta` int(11)
,`points_balance` int(10) unsigned
,`created_at` datetime
);

-- Cafe activity summary — all-time totals per cafe
DROP TABLE IF EXISTS `vw_cafe_activity`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_cafe_activity` AS
SELECT `c`.`cafe_id` AS `cafe_id`, `c`.`name` AS `cafe_name`,
       count(distinct `t`.`user_id`) AS `unique_customers`,
       count(`t`.`transaction_id`) AS `total_transactions`,
       sum(case when `t`.`transaction_type` = 'earn' then `t`.`points_delta` else 0 end) AS `total_points_awarded`,
       sum(case when `t`.`transaction_type` = 'redeem' then abs(`t`.`points_delta`) else 0 end) AS `total_points_redeemed`
FROM (`cafes` `c` left join `transactions` `t` on(`c`.`cafe_id` = `t`.`cafe_id`))
WHERE `c`.`is_active` = 1
GROUP BY `c`.`cafe_id`, `c`.`name`;

-- Leaderboard view — top customers ranked by points balance
DROP TABLE IF EXISTS `vw_leaderboard`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_leaderboard` AS
SELECT `u`.`user_id` AS `user_id`, `u`.`full_name` AS `full_name`,
       `u`.`points_balance` AS `points_balance`,
       count(`t`.`transaction_id`) AS `total_visits`,
       rank() over (order by `u`.`points_balance` desc) AS `rank_position`
FROM (`users` `u` left join `transactions` `t` on(`u`.`user_id` = `t`.`user_id` and `t`.`transaction_type` = 'earn'))
WHERE `u`.`is_active` = 1
GROUP BY `u`.`user_id`, `u`.`full_name`, `u`.`points_balance`;

-- Customer transaction history view — newest first
DROP TABLE IF EXISTS `vw_user_history`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_user_history` AS
SELECT `t`.`transaction_id` AS `transaction_id`, `u`.`full_name` AS `customer_name`,
       `u`.`email` AS `customer_email`, `c`.`name` AS `cafe_name`,
       `t`.`transaction_type` AS `transaction_type`, `t`.`points_delta` AS `points_delta`,
       `u`.`points_balance` AS `points_balance`, `t`.`created_at` AS `created_at`
FROM ((`transactions` `t`
  join `users` `u` on(`t`.`user_id` = `u`.`user_id`))
  join `cafes` `c` on(`t`.`cafe_id` = `c`.`cafe_id`))
ORDER BY `t`.`created_at` DESC;

--
-- Indexes for dumped tables
--

ALTER TABLE `cafes`
  ADD PRIMARY KEY (`cafe_id`),
  ADD KEY `idx_cafes_city`     (`city`),
  ADD KEY `idx_cafes_postcode` (`postcode`),
  ADD KEY `idx_cafes_active`   (`is_active`);

ALTER TABLE `cafe_staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `uq_staff_email`  (`email`),
  ADD KEY `idx_staff_cafe_id`  (`cafe_id`),
  ADD KEY `idx_staff_is_active` (`is_active`);

ALTER TABLE `qr_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD UNIQUE KEY `uq_qr_token_value` (`token_value`),
  ADD UNIQUE KEY `uq_qr_nonce`       (`nonce`),
  ADD KEY `idx_qr_user_id` (`user_id`),
  ADD KEY `idx_qr_expires`  (`expires_at`),
  ADD KEY `idx_qr_is_used`  (`is_used`);

ALTER TABLE `rewards`
  ADD PRIMARY KEY (`reward_id`),
  ADD KEY `idx_rewards_active` (`is_active`);

ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `idx_sess_last_activity` (`last_activity`),
  ADD KEY `idx_sess_user_id`       (`user_id`),
  ADD KEY `idx_sess_staff_id`      (`staff_id`);

ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_tx_user_id`    (`user_id`),
  ADD KEY `idx_tx_cafe_id`    (`cafe_id`),
  ADD KEY `idx_tx_staff_id`   (`staff_id`),
  ADD KEY `idx_tx_created_at` (`created_at`),
  ADD KEY `fk_tx_token`       (`token_id`),
  ADD KEY `fk_tx_reward`      (`reward_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_users_email`   (`email`),
  ADD KEY `idx_users_is_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

ALTER TABLE `cafes`        MODIFY `cafe_id`        int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `cafe_staff`   MODIFY `staff_id`        int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `qr_tokens`    MODIFY `token_id`        int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;
ALTER TABLE `rewards`      MODIFY `reward_id`       int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `transactions` MODIFY `transaction_id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `users`        MODIFY `user_id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

ALTER TABLE `cafe_staff`
  ADD CONSTRAINT `fk_staff_cafe`
    FOREIGN KEY (`cafe_id`) REFERENCES `cafes` (`cafe_id`) ON UPDATE CASCADE;

ALTER TABLE `qr_tokens`
  ADD CONSTRAINT `fk_qr_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_tx_cafe`
    FOREIGN KEY (`cafe_id`)   REFERENCES `cafes`      (`cafe_id`)   ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tx_reward`
    FOREIGN KEY (`reward_id`) REFERENCES `rewards`    (`reward_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tx_staff`
    FOREIGN KEY (`staff_id`)  REFERENCES `cafe_staff` (`staff_id`)  ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tx_token`
    FOREIGN KEY (`token_id`)  REFERENCES `qr_tokens`  (`token_id`)  ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tx_user`
    FOREIGN KEY (`user_id`)   REFERENCES `users`      (`user_id`)   ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
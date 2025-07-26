CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(50) NOT NULL,
  `first_name` VARCHAR(255) NOT NULL,
  `middle_name` VARCHAR(255) NOT NULL,
  `last_name` VARCHAR(255) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pet_queue` (
  `queue_id` INT AUTO_INCREMENT PRIMARY KEY,
  `queue_number` VARCHAR(50) DEFAULT NULL,
  `owner_name` VARCHAR(255) NOT NULL,
  `pet_name` VARCHAR(255) NOT NULL,
  `service_type` VARCHAR(100) NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Waiting',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` INT,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pet_owner_records` (
  `owner_id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(255) NOT NULL,
  `middle_name` VARCHAR(255) NULL,
  `last_name` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `mobile_number` VARCHAR(255) NOT NULL,
  `messenger_account` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pet_records` (
  `pet_id` INT AUTO_INCREMENT PRIMARY KEY,
  `pet_code` VARCHAR(10) UNIQUE NOT NULL,
  `owner_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `species` VARCHAR(255) NOT NULL,
  `breed` VARCHAR(255) NOT NULL,
  `color` VARCHAR(255) NOT NULL,
  `sex` VARCHAR(255) NOT NULL,
  `birthdate` DATE NOT NULL,
  `photo` VARCHAR(255) NOT NULL,
  `age` VARCHAR(255) NOT NULL,
  `markings` VARCHAR(255) NOT NULL,
  FOREIGN KEY (`owner_id`) REFERENCES `pet_owner_records`(`owner_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `products` (
  `product_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_code` VARCHAR(10) UNIQUE NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` VARCHAR(500),
  `price` DECIMAL(10,2) NOT NULL,
  `unit_of_measure` VARCHAR(250) NOT NULL,
  `category` VARCHAR(250) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 0,
  `stock` VARCHAR(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `sales` (
  `sale_id` INT AUTO_INCREMENT PRIMARY KEY,
  `others_date` DATE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `product_sale` (
  `product_sale_id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NULL,
  `sale_id` INT NULL,
  `sale_price` DECIMAL(10,2) NOT NULL,
  `sale_quantity` INT NOT NULL DEFAULT 1,
  `unit_of_measure` VARCHAR(250) NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
  FOREIGN KEY (`sale_id`) REFERENCES `sales`(`sale_id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Medical Records Table (already linked to pet and user)
CREATE TABLE `medical_records` (
  `medical_record_id` INT AUTO_INCREMENT PRIMARY KEY,
  `pet_id` INT NULL,
  `owner_id` INT NULL, -- attending vet or staff
  `type` VARCHAR(255) NOT NULL,
  `date_started` DATE NOT NULL,
  `date_ended` DATE NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `weight` VARCHAR(255) NOT NULL,
  `temperature` VARCHAR(255) NOT NULL,
  `complaint` VARCHAR(255) NOT NULL,

  `treatment_date` DATE,
  `treatment_name` VARCHAR(255),
  `treatment_test` VARCHAR(255),
  `treatment_remarks` VARCHAR(255),
  `treatment_charge` DECIMAL(10,2),

  `prescription_date` DATE,
  `prescription_name` VARCHAR(255),
  `prescription_description` VARCHAR(255),
  `prescription_remarks` VARCHAR(255),
  `prescription_charge` DECIMAL(10,2),

  `others_date` DATE,
  `others_name` VARCHAR(255),
  `others_quantity` VARCHAR(255),
  `others_remarks` VARCHAR(255),
  `others_charge` DECIMAL(10,2),

  FOREIGN KEY (`pet_id`) REFERENCES `pet_records`(`pet_id`) ON DELETE CASCADE,
  FOREIGN KEY (`owner_id`) REFERENCES `pet_owner_records`(`owner_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `medical_bill` (
  `bill_id` INT AUTO_INCREMENT PRIMARY KEY,
  `medical_record_id` INT NULL,
  `owner_id` INT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` VARCHAR(100) NOT NULL, 
  `billing_date` DATE NOT NULL,
  FOREIGN KEY (`medical_record_id`) REFERENCES `medical_records`(`medical_record_id`) ON DELETE CASCADE,
  FOREIGN KEY (`owner_id`) REFERENCES `pet_owner_records`(`owner_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE payment_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  medical_record_id INT NOT NULL,
  payment_amount DECIMAL(10, 2) NOT NULL,
  payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (medical_record_id) REFERENCES medical_records(medical_record_id) ON DELETE CASCADE
);

CREATE TABLE `dogs` (
  `dog_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cats` (
  `cat_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `dogs` (`name`) VALUES
('Akita'),
('American Bulldog'),
('Australian Shepherd'),
('Alaskan Malamute'),
('Airedale Terrier'),
('Anatolian Shepherd'),
('Afghan Hound'),
('Beagle'),
('Boxer'),
('Boston Terrier'),
('Border Collie'),
('Bichon Frise'),
('Bernese Mountain Dog'),
('Bull Terrier'),
('Belgian Malinois'),
('Bloodhound'),
('Chihuahua'),
('Cocker Spaniel'),
('Cavalier King Charles Spaniel'),
('Chow Chow'),
('Collie'),
('Chesapeake Bay Retriever'),
('Cairn Terrier'),
('Cardigan Welsh Corgi'),
('Clumber Spaniel'),
('Dachshund'),
('Dalmatian'),
('Doberman Pinscher'),
('Dogo Argentino'),
('Dutch Shepherd'),
('Dandie Dinmont Terrier'),
('English Bulldog'),
('English Setter'),
('English Springer Spaniel'),
('Entlebucher Mountain Dog'),
('English Foxhound'),
('French Bulldog'),
('Flat-Coated Retriever'),
('Finnish Spitz'),
('Finnish Lapphund'),
('Field Spaniel'),
('Fox Terrier'),
('Golden Retriever'),
('German Shepherd'),
('Great Dane'),
('Greyhound'),
('Glen of Imaal Terrier'),
('Gordon Setter'),
('Giant Schnauzer'),
('German Shorthaired Pointer'),
('Husky (Siberian Husky)'),
('Havanese'),
('Harrier'),
('Hungarian Vizsla'),
('Hungarian Puli'),
('Hound (various breeds)'),
('Irish Setter'),
('Irish Terrier'),
('Irish Wolfhound'),
('Icelandic Sheepdog'),
('Italian Greyhound'),
('Jack Russell Terrier'),
('Japanese Chin'),
('Japanese Spitz'),
('King Charles Spaniel'),
('Keeshond'),
('Kerry Blue Terrier'),
('Kuvasz'),
('Komondor'),
('Labrador Retriever'),
('Lhasa Apso'),
('Lowchen'),
('Lagotto Romagnolo'),
('Maltese'),
('Mastiff (English Mastiff)'),
('Miniature Schnauzer'),
('Manchester Terrier'),
('Norwegian Lundehund'),
('Newfoundland'),
('Norfolk Terrier'),
('Neapolitan Mastiff'),
('Norwegian Elkhound'),
('Otterhound'),
('Old English Sheepdog'),
('Olde English Bulldogge'),
('Poodle'),
('Pembroke Welsh Corgi'),
('Papillon'),
('Pug'),
('Portuguese Water Dog'),
('Polish Lowland Sheepdog'),
('Queensland Heeler (Australian Cattle Dog)'),
('Rottweiler'),
('Rhodesian Ridgeback'),
('Russian Toy'),
('Rat Terrier'),
('Shih Tzu'),
('Samoyed'),
('Saint Bernard'),
('Scottish Terrier'),
('Staffordshire Bull Terrier'),
('Shar Pei'),
('Tibetan Mastiff'),
('Toy Fox Terrier'),
('Treeing Walker Coonhound'),
('Tervuren'),
('Utonagan'),
('Vizsla'),
('Volpino Italiano'),
('Weimaraner'),
('Welsh Springer Spaniel'),
('West Highland White Terrier'),
('Whippet'),
('Wire Fox Terrier'),
('Xoloitzcuintli (Mexican Hairless Dog)'),
('Yorkshire Terrier'),
('Zwergspitz (Pomeranian)'),
('Zuchon (Shichon, Shih Tzu + Bichon mix)');

INSERT INTO `cats` (`name`) VALUES
('Siamese'),
('Persian'),
('Maine Coon'),
('Ragdoll'),
('British Shorthair'),
('Bengal'),
('Sphynx'),
('Scottish Fold'),
('Abyssinian'),
('Birman'),
('Oriental Shorthair'),
('Devon Rex'),
('Russian Blue'),
('Norwegian Forest Cat'),
('Exotic Shorthair'),
('Turkish Angora'),
('Cornish Rex'),
('Himalayan'),
('Balinese'),
('Tonkinese'),
('American Shorthair'),
('Manx'),
('LaPerm'),
('Singapura'),
('Somali'),
('Ocicat'),
('Bombay'),
('Chartreux'),
('Turkish Van'),
('Ragamuffin');
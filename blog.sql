-- Create database
CREATE DATABASE IF NOT EXISTS blog;
USE blog;

-- User details table
CREATE TABLE IF NOT EXISTS user_details (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  user_name VARCHAR(50) NOT NULL UNIQUE,
  user_email VARCHAR(100) NOT NULL UNIQUE,
  user_password VARCHAR(255) NOT NULL,
  user_role ENUM('admin', 'author') NOT NULL DEFAULT 'author',
  create_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  update_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status_del TINYINT(1) NOT NULL DEFAULT 1
);

-- User tokens table for remember me functionality
CREATE TABLE IF NOT EXISTS user_tokens (
  token_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE,
  expires DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES user_details(user_id) ON DELETE CASCADE
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(50) NOT NULL UNIQUE,
  category_description TEXT,
  create_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  update_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status_del TINYINT(1) NOT NULL DEFAULT 1
);

-- Posts table
CREATE TABLE IF NOT EXISTS posts_details (
  post_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  r_author_id INT NOT NULL,
  r_category_id INT NOT NULL,
  image_link VARCHAR(255),
  post_status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  create_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  update_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status_del TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (r_author_id) REFERENCES user_details(user_id),
  FOREIGN KEY (r_category_id) REFERENCES categories(category_id)
);

-- Insert default admin user (password: admin123)
INSERT INTO user_details (user_name, user_email, user_password, user_role)
VALUES ('admin', 'admin@example.com', '$2y$10$8KQX.9iq8bEBIBpTqFa.3.YGGcNH7hQKKocgFT.Ot9XwgQGaEE3Uy', 'admin');

-- Insert default categories
INSERT INTO categories (category_name, category_description)
VALUES 
('Technology', 'Articles about the latest technology trends and innovations'),
('Travel', 'Explore destinations around the world'),
('Food', 'Recipes, restaurant reviews, and culinary adventures'),
('Health', 'Tips for maintaining a healthy lifestyle'),
('Business', 'Business news, entrepreneurship, and career advice');

-- Insert sample posts
INSERT INTO posts_details (title, content, r_author_id, r_category_id, post_status)
VALUES 
('Getting Started with PHP', 'PHP is a popular server-side scripting language that is especially suited for web development. PHP is fast, flexible and pragmatic. It powers everything from your blog to the most popular websites in the world.\n\nHere are some key features of PHP:\n\n1. Server-side scripting: PHP code is executed on the server, generating HTML which is then sent to the client.\n\n2. Cross-platform: PHP runs on various platforms (Windows, Linux, Unix, Mac OS X, etc.)\n\n3. Compatibility with almost all servers used today (Apache, IIS, etc.)\n\n4. Support for a wide range of databases\n\n5. Free and open source\n\nTo get started with PHP, you need a web server with PHP installed. You can download PHP from the official website and follow the installation instructions.', 1, 1, 'published'),
('Top 10 Travel Destinations for 2023', 'Planning your next vacation? Here are the top destinations for 2023:\n\n1. Bali, Indonesia - Known for its beautiful beaches, lush rice terraces, and vibrant culture.\n\n2. Tokyo, Japan - A bustling metropolis with a perfect blend of traditional and modern attractions.\n\n3. Barcelona, Spain - Famous for its unique architecture, delicious food, and vibrant nightlife.\n\n4. Cape Town, South Africa - Offers stunning landscapes, wildlife, and cultural experiences.\n\n5. New York City, USA - The city that never sleeps, with iconic landmarks and diverse neighborhoods.\n\n6. Santorini, Greece - Known for its white-washed buildings, blue domes, and breathtaking sunsets.\n\n7. Queenstown, New Zealand - Adventure capital with stunning natural beauty.\n\n8. Marrakech, Morocco - A vibrant city with colorful markets, palaces, and gardens.\n\n9. Reykjavik, Iceland - Gateway to natural wonders like geysers, waterfalls, and the Northern Lights.\n\n10. Kyoto, Japan - Home to numerous temples, shrines, and traditional gardens.', 1, 2, 'published'),
('Easy Pasta Recipes for Beginners', 'These simple pasta recipes are perfect for beginners:\n\n1. Classic Spaghetti Aglio e Olio\nIngredients:\n- 1 pound spaghetti\n- 6 cloves garlic, thinly sliced\n- 1/2 cup olive oil\n- 1/4 teaspoon red pepper flakes\n- Salt and pepper to taste\n- Fresh parsley, chopped\n\nInstructions:\n1. Cook pasta according to package instructions.\n2. Heat olive oil in a pan and add garlic, cooking until golden.\n3. Add red pepper flakes, salt, and pepper.\n4. Toss with drained pasta and garnish with parsley.\n\n2. Simple Tomato Pasta\nIngredients:\n- 1 pound pasta of your choice\n- 2 tablespoons olive oil\n- 1 onion, diced\n- 3 cloves garlic, minced\n- 1 can (28 oz) crushed tomatoes\n- 1 teaspoon dried basil\n- Salt and pepper to taste\n- Grated Parmesan cheese\n\nInstructions:\n1. Cook pasta according to package instructions.\n2. In a pan, heat olive oil and sauté onion until translucent.\n3. Add garlic and cook for 30 seconds.\n4. Add crushed tomatoes, basil, salt, and pepper.\n5. Simmer for 15 minutes, then toss with pasta.\n6. Serve with grated Parmesan cheese.', 1, 3, 'published'),
('The Benefits of Regular Exercise', 'Regular exercise has numerous health benefits that go beyond weight management. Here are some key advantages of maintaining a consistent exercise routine:\n\n1. Improved Cardiovascular Health\nRegular physical activity strengthens your heart and improves circulation. This leads to lower blood pressure, reduced risk of heart disease, and better cholesterol levels.\n\n2. Enhanced Mental Health\nExercise releases endorphins, which are known as "feel-good" hormones. This can help reduce stress, anxiety, and depression while improving overall mood and cognitive function.\n\n3. Increased Energy Levels\nContrary to what you might expect, regular physical activity can actually boost your energy levels rather than deplete them. Exercise improves muscle strength and endurance, giving you more energy for daily activities.\n\n4. Better Sleep Quality\nRegular exercise can help you fall asleep faster and enjoy deeper sleep. Just be careful not to exercise too close to bedtime, as it might interfere with your ability to fall asleep.\n\n5. Stronger Immune System\nModerate, regular exercise can strengthen your immune system and reduce the risk of certain illnesses.\n\n6. Weight Management\nExercise helps burn calories and build muscle, which can help maintain a healthy weight or support weight loss goals when combined with a balanced diet.\n\n7. Improved Bone Health\nWeight-bearing exercises like walking, running, and strength training help build and maintain bone density, reducing the risk of osteoporosis as you age.\n\n8. Enhanced Cognitive Function\nRegular physical activity has been shown to improve memory, concentration, and overall brain function, potentially reducing the risk of cognitive decline and dementia.\n\nRemember, you don\'t need to become a marathon runner or weightlifting champion to enjoy these benefits. Even  you don\'t need to become a marathon runner or weightlifting champion to enjoy these benefits. Even moderate activity like a 30-minute daily walk can significantly improve your health. The key is consistency - aim for at least 150 minutes of moderate aerobic activity or 75 minutes of vigorous activity each week, along with strength training exercises twice a week.

Always consult with your healthcare provider before starting a new exercise program, especially if you have any existing health conditions.', 1, 4, 'draft'),
('Starting Your Own Business: A Guide', 'Thinking about starting your own business? Here\'s what you need to know to get started:\n\n1. Identify Your Business Idea\nStart by identifying a business idea that aligns with your skills, interests, and market demand. Research your target market to ensure there\'s a need for your product or service.\n\n2. Create a Business Plan\nA solid business plan is essential for success. Include your business description, market analysis, organizational structure, product/service details, marketing strategy, and financial projections.\n\n3. Secure Funding\nDetermine how much capital you need and explore funding options such as personal savings, loans, investors, or crowdfunding. Be realistic about startup costs and ongoing expenses.\n\n4. Choose a Business Structure\nDecide on a legal structure for your business (sole proprietorship, partnership, LLC, corporation). Each has different legal and tax implications.\n\n5. Register Your Business\nRegister your business name, obtain necessary licenses and permits, and apply for an Employer Identification Number (EIN) if needed.\n\n6. Set Up Business Banking\nOpen a separate business bank account to keep personal and business finances separate. Consider getting a business credit card for expenses.\n\n7. Create a Marketing Strategy\nDevelop a comprehensive marketing plan to reach your target audience. This may include a website, social media presence, content marketing, and traditional advertising.\n\n8. Build Your Team\nDecide if you need employees or contractors. Define roles clearly and create a positive work culture from the start.\n\n9. Launch and Adapt\nAfter launching, be prepared to adapt based on customer feedback and market changes. Continuous improvement is key to long-term success.\n\n10. Maintain Work-Life Balance\nRunning a business can be all-consuming. Set boundaries to maintain your well-being and prevent burnout.\n\nRemember that most successful businesses don\'t happen overnight. Be patient, persistent, and willing to learn from both successes and failures.', 1, 5, 'published');

-- Create uploads directory
-- Note: This would typically be done in PHP code, not SQL

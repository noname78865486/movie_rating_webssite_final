CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id  INT NOT NULL,
    rating_user_idNum  INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    rating FLOAT NOT NULL,
    visibility ENUM('공개', '비공개') DEFAULT '비공개',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    file_path VARCHAR(255),
    FOREIGN KEY (rating_user_idNum) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
);
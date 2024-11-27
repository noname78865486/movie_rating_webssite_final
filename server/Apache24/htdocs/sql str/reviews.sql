CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id  INT NOT NULL,
    userID  VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    rating FLOAT NOT NULL,
    visibility ENUM('public', 'private') DEFAULT 'public',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    file_path VARCHAR(255),
	FOREIGN KEY (userID) REFERENCES users(userID) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
);
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL, -- 리뷰 ID와 연관
    user_id INT NOT NULL, -- 댓글 작성자 ID
    content TEXT NOT NULL, -- 댓글 내용
    visibility ENUM('공개', '비공개'), -- 댓글 공개 여부 선택
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- 작성 시간
    FOREIGN KEY (review_id) REFERENCES reviews(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
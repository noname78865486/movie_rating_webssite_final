SELECT m.id, 
       COALESCE(m.title, '') AS title, 
       COALESCE(m.director, '') AS director, 
       COALESCE(m.release_date, '') AS release_date, 
       COALESCE(m.genre, '') AS genre,
       COALESCE(AVG(r.rating), 0) AS avg_rating
FROM movies m
LEFT JOIN reviews r ON m.id = r.movie_id
GROUP BY m.id
ORDER BY m.id DESC;

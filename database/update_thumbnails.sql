-- Update thumbnail / poster untuk GrandBlue dan Charlotte
UPDATE dramas 
SET poster_url = 'assets/posters/grandblue.jpg' 
WHERE LOWER(title) LIKE '%grand%blue%' OR LOWER(slug) LIKE '%grand%blue%';

UPDATE dramas 
SET poster_url = 'assets/posters/charlotte.jpg' 
WHERE LOWER(title) LIKE '%charlotte%' OR LOWER(slug) LIKE '%charlotte%';

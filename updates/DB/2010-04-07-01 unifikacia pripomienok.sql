UPDATE menu set name='Pripomienky' WHERE href='all/suggestion/index';
DELETE FROM menu WHERE href='all/suggestion/add';
UPDATE menu SET poradie=poradie-1 WHERE poradie>18;
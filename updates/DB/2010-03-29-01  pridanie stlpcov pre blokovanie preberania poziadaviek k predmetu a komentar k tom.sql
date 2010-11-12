-- Pridanie stlpcov
ALTER TABLE predmet ADD COLUMN blokovat_preberanie boolean;
ALTER TABLE predmet ADD COLUMN dovod_blokovania TEXT;

-- Nastavenie default hodnoty
ALTER TABLE predmet ALTER COLUMN blokovat_preberanie SET DEFAULT FALSE;

UPDATE predmet SET blokovat_preberanie = FALSE WHERE blokovat_preberanie IS NULL;

-- Nastavenie komentara
COMMENT ON COLUMN predmet.blokovat_preberanie IS 'Flag, ktory urcuje, ci je nastavene blokovanie preberania poziadaviek z minuleho roku pre dany predmet';
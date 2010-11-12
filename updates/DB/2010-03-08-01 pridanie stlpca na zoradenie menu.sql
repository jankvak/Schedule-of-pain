-- Pridanie stlca
ALTER TABLE menu ADD COLUMN poradie integer;
COMMENT ON COLUMN menu.poradie IS 'definuje poradie, v akom sa zobrazuju polozky v menu';

-- Naplnenie noveho stlpca
UPDATE menu
SET poradie = 1
WHERE href = 'administrator/users/index';

UPDATE menu
SET poradie = 2
WHERE href = 'administrator/suggestions/index';

UPDATE menu
SET poradie = 3
WHERE href = 'administrator/log/view';

UPDATE menu
SET poradie = 4
WHERE href = 'ape/periods/index';

UPDATE menu
SET poradie = 5
WHERE href = 'ape/subjects/index';

UPDATE menu
SET poradie = 6
WHERE href = 'ape/lessons/index';

UPDATE menu
SET poradie = 7
WHERE href = 'ape/rooms/index';

UPDATE menu
SET poradie = 8
WHERE href = 'ape/equipment/index';

UPDATE menu
SET poradie = 9
WHERE href = 'garant/requirements/index';

UPDATE menu
SET poradie = 10
WHERE href = 'teacher/requirements/index';

UPDATE menu
SET poradie = 11
WHERE href = 'pract/requirements/index';

UPDATE menu
SET poradie = 12
WHERE href = 'scheduler/req_prehlad/index';

UPDATE menu
SET poradie = 13
WHERE href = 'scheduler/req_prednaska/index';

UPDATE menu
SET poradie = 14
WHERE href = 'scheduler/req_cvicenie/index';

UPDATE menu
SET poradie = 15
WHERE href = 'scheduler/priorities/index';

UPDATE menu
SET poradie = 16
WHERE href = 'all/priorities/index';

UPDATE menu
SET poradie = 17
WHERE href = 'all/suggestion/add';

UPDATE menu
SET poradie = 18
WHERE href = 'all/suggestion/index';


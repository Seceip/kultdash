USE db_kultive;
SET SQL_SAFE_UPDATES = 0;

DELETE FROM arquivos WHERE prontuario_id IN (SELECT prontuario_id FROM prontuario WHERE prop_id IN (SELECT prop_id FROM propriedade WHERE nome IN ('Fazenda Santa Clara','Sítio Boa Vista')));
DELETE FROM prontuario WHERE prop_id IN (SELECT prop_id FROM propriedade WHERE nome IN ('Fazenda Santa Clara','Sítio Boa Vista'));
DELETE FROM animais WHERE prop_id IN (SELECT prop_id FROM propriedade WHERE nome IN ('Fazenda Santa Clara','Sítio Boa Vista'));
DELETE FROM maquinas_equipamentos WHERE prop_id IN (SELECT prop_id FROM propriedade WHERE nome IN ('Fazenda Santa Clara','Sítio Boa Vista'));
DELETE FROM area_cultivo WHERE prop_id IN (SELECT prop_id FROM propriedade WHERE nome IN ('Fazenda Santa Clara','Sítio Boa Vista'));
DELETE FROM usuario_propriedade WHERE prop_id IN (SELECT prop_id FROM propriedade WHERE nome IN ('Fazenda Santa Clara','Sítio Boa Vista'));
DELETE FROM propriedade WHERE nome IN ('Fazenda Santa Clara','Sítio Boa Vista');

SET SQL_SAFE_UPDATES = 1;

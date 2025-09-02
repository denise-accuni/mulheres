1) Governo — mulheres em CT&I nos últimos 20 anos
-- Parâmetros
-- :inicio = '2005-01-01'   :fim = '2025-12-31'

SELECT
  p.nome                         AS "Nome",
  p.pais_origem                  AS "País (origem)",
  o.nome                         AS "Órgão",
  c.titulo                       AS "Função",
  c.inicio, c.fim,
  c.setor,
  COALESCE(i.descricao_curta, '') AS "Impacto (resumo)",
  f.url                          AS "Fonte (link verificado)"
FROM CARGO c
JOIN PESSOA p       ON p.person_id = c.person_id AND p.sexo = 'F'
JOIN ORGANIZACAO o  ON o.org_id    = c.org_id    AND o.tipo = 'governo'
LEFT JOIN IMPACTO i ON i.person_id = p.person_id
LEFT JOIN FONTE   f ON f.fonte_id  = i.fonte_id
WHERE c.inicio >= :inicio AND COALESCE(c.fim, :fim) <= :fim
  AND c.setor IN ('ciencia','tecnologia','inovacao','comunicacoes','digital','telecom');

(a) Linha do tempo por ano
SELECT EXTRACT(YEAR FROM c.inicio) AS ano, COUNT(*) AS qtde
FROM CARGO c
JOIN PESSOA p ON p.person_id = c.person_id AND p.sexo = 'F'
JOIN ORGANIZACAO o ON o.org_id = c.org_id AND o.tipo = 'governo'
WHERE c.inicio >= :inicio AND COALESCE(c.fim, :fim) <= :fim
  AND c.setor IN ('ciencia','tecnologia','inovacao','comunicacoes','digital','telecom')
GROUP BY EXTRACT(YEAR FROM c.inicio)
ORDER BY ano;

2) Empresas — líderes mulheres em tecnologia (últimos 20 anos)
SELECT
  p.nome,
  p.pais_origem            AS "País (origem)",
  o.nome                   AS "Empresa",
  c.titulo                 AS "Função",
  c.inicio, c.fim,
  c.setor,
  COALESCE(i.descricao_curta,'') AS "Impacto (resumo)",
  f.url                    AS "Fonte"
FROM CARGO c
JOIN PESSOA p      ON p.person_id = c.person_id AND p.sexo = 'F'
JOIN ORGANIZACAO o ON o.org_id    = c.org_id    AND o.tipo = 'empresa'
LEFT JOIN IMPACTO i ON i.person_id = p.person_id
LEFT JOIN FONTE   f ON f.fonte_id  = i.fonte_id
WHERE c.inicio >= :inicio AND COALESCE(c.fim, :fim) <= :fim
  AND (LOWER(c.titulo) SIMILAR TO '(ceo|coo|cfo|cio|cto|chair|president)%'
       OR LOWER(c.setor) IN ('ia','nuvem','semicondutores','ciberseguranca','software','hardware'))
ORDER BY o.nome, c.inicio DESC;

(a) Top países de origem
SELECT p.pais_origem, COUNT(*) AS qtde
FROM CARGO c
JOIN PESSOA p ON p.person_id=c.person_id AND p.sexo='F'
JOIN ORGANIZACAO o ON o.org_id=c.org_id AND o.tipo='empresa'
WHERE c.inicio>=:inicio AND COALESCE(c.fim,:fim)<=:fim
GROUP BY p.pais_origem
ORDER BY qtde DESC;

3) Eventos — presença de mulheres por evento/ano (com país da mulher e do evento)
SELECT
  e.ano                     AS "Ano",
  e.nome                    AS "Evento",
  p.nome                    AS "Nome",
  'F'                       AS "Sexo",
  pe.papel                  AS "Atuação",
  COALESCE(i.descricao_curta,'') AS "Impactos",
  p.pais_origem             AS "País da mulher",
  e.pais                    AS "País do evento",
  pe.fonte_url              AS "Fonte (link verificado)"
FROM PARTICIPACAO_EVENTO pe
JOIN EVENTO e  ON e.evento_id  = pe.evento_id
JOIN PESSOA p  ON p.person_id  = pe.person_id AND p.sexo='F'
LEFT JOIN IMPACTO i ON i.person_id = p.person_id
WHERE e.ano BETWEEN 2005 AND 2025
ORDER BY e.ano DESC, e.nome, p.nome;

(a) Tabela-resumo: mulheres por evento/ano
SELECT e.ano, e.nome AS evento, COUNT(*) AS mulheres_no_palco
FROM PARTICIPACAO_EVENTO pe
JOIN EVENTO e ON e.evento_id=pe.evento_id
JOIN PESSOA p ON p.person_id=pe.person_id AND p.sexo='F'
GROUP BY e.ano, e.nome
ORDER BY e.ano DESC, mulheres_no_palco DESC;


Se você também cadastrar participações de homens (ou sexo = 'M'/'NB'), dá para calcular % de mulheres por evento:

WITH total AS (
  SELECT e.evento_id, COUNT(*) AS total_speakers
  FROM PARTICIPACAO_EVENTO pe JOIN EVENTO e ON e.evento_id=pe.evento_id
  GROUP BY e.evento_id
),
mulheres AS (
  SELECT e.evento_id, COUNT(*) AS mulheres
  FROM PARTICIPACAO_EVENTO pe
  JOIN EVENTO e ON e.evento_id=pe.evento_id
  JOIN PESSOA p ON p.person_id=pe.person_id AND p.sexo='F'
  GROUP BY e.evento_id
)
SELECT e.ano, e.nome AS evento,
       m.mulheres, t.total_speakers,
       ROUND(100.0*m.mulheres/t.total_speakers,2) AS pct_mulheres
FROM EVENTO e
JOIN total t   ON t.evento_id=e.evento_id
LEFT JOIN mulheres m ON m.evento_id=e.evento_id
ORDER BY e.ano DESC, pct_mulheres DESC;

4) Apenas fontes “A” e “B” (confiabilidade)
SELECT p.nome, o.nome AS organizacao, c.titulo, i.descricao_curta, f.url, f.tipo, f.qualidade
FROM CARGO c
JOIN PESSOA p ON p.person_id=c.person_id AND p.sexo='F'
JOIN ORGANIZACAO o ON o.org_id=c.org_id
LEFT JOIN IMPACTO i ON i.person_id=p.person_id
LEFT JOIN FONTE   f ON f.fonte_id=i.fonte_id
WHERE f.qualidade IN ('A','B');

5) “Vistas” para exportar como as suas planilhas

Crie views que já devolvem o formato final:

CREATE VIEW v_mulheres_governo_20anos AS
SELECT
  p.nome AS "Nome",
  p.pais_origem AS "País",
  c.titulo AS "Função",
  COALESCE(i.descricao_curta,'') AS "Impacto (resumo)",
  COALESCE(f.url,'') AS "Fonte"
FROM CARGO c
JOIN PESSOA p ON p.person_id=c.person_id AND p.sexo='F'
JOIN ORGANIZACAO o ON o.org_id=c.org_id AND o.tipo='governo'
LEFT JOIN IMPACTO i ON i.person_id=p.person_id
LEFT JOIN FONTE   f ON f.fonte_id=i.fonte_id
WHERE c.inicio >= '2005-01-01' AND COALESCE(c.fim,'2025-12-31') <= '2025-12-31';


Para empresas e eventos, repita a view mudando o.tipo/joins de EVENTO/PARTICIPACAO_EVENTO.

6) Índices (mantém a pesquisa “leve”, mesmo com muitos anos)
CREATE INDEX idx_cargo_periodo   ON CARGO(inicio, fim);
CREATE INDEX idx_cargo_org       ON CARGO(org_id);
CREATE INDEX idx_cargo_pessoa    ON CARGO(person_id);
CREATE INDEX idx_evento_ano      ON EVENTO(ano);
CREATE INDEX idx_particip_evento ON PARTICIPACAO_EVENTO(evento_id, person_id);
CREATE INDEX idx_fonte_qualidade ON FONTE(qualidade);


Observação técnica rápida

SQLite: troque EXTRACT(YEAR FROM data) por CAST(STRFTIME('%Y', data) AS INT) e SIMILAR TO por LIKE/regex.

Postgres: mantenha como está; use STRING_AGG se quiser consolidar vários impactos numa só linha.

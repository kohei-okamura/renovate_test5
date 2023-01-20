CREATE TABLE main (
  service_code             TEXT    NOT NULL,
  name                     TEXT    NOT NULL,
  category                 INTEGER NOT NULL,
  is_secondary             INTEGER NOT NULL,
  is_coaching              INTEGER NOT NULL,
  is_hospitalized          INTEGER NOT NULL,
  is_long_hospitalized     INTEGER NOT NULL,
  score                    INTEGER NOT NULL,
  timeframe                INTEGER NOT NULL,
  duration_start           INTEGER NOT NULL,
  duration_end             INTEGER NOT NULL,
  unit                     INTEGER NOT NULL
);

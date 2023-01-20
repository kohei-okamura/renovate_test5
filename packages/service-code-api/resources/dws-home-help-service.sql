CREATE TABLE main (
  service_code             TEXT    NOT NULL,
  name                     TEXT    NOT NULL,
  category                 INTEGER NOT NULL,
  is_extra                 INTEGER NOT NULL,
  is_secondary             INTEGER NOT NULL,
  provider_type            INTEGER NOT NULL,
  is_planned_by_novice     INTEGER NOT NULL,
  building_type            INTEGER NOT NULL,
  score                    INTEGER NOT NULL,
  daytime_duration_start   INTEGER NOT NULL,
  daytime_duration_end     INTEGER NOT NULL,
  morning_duration_start   INTEGER NOT NULL,
  morning_duration_end     INTEGER NOT NULL,
  night_duration_start     INTEGER NOT NULL,
  night_duration_end       INTEGER NOT NULL,
  midnight_duration1_start INTEGER NOT NULL,
  midnight_duration1_end   INTEGER NOT NULL,
  midnight_duration2_start INTEGER NOT NULL,
  midnight_duration2_end   INTEGER NOT NULL
);

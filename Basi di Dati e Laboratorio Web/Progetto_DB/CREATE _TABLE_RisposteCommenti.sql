CREATE TABLE `RisposteCommenti` (
  `id_commento_risposta` bigint(20) unsigned NOT NULL COMMENT 'ID della risposta.',
  `id_commento_riferimento` bigint(20) unsigned NOT NULL COMMENT 'ID del commento di riferimento (quello a cui la risposta sta rispondendo)',
  PRIMARY KEY (`id_commento_risposta`,`id_commento_riferimento`),
  CONSTRAINT FK_Commento_Riferimento FOREIGN KEY (`id_commento_riferimento`)
  REFERENCES Commenti(`id_commento`)
  ON UPDATE CASCADE
  ON DELETE CASCADE,
  CONSTRAINT FK_Commento_Risposta FOREIGN KEY (`id_commento_risposta`)
  REFERENCES Commenti(`id_commento`)
  ON UPDATE CASCADE
  ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
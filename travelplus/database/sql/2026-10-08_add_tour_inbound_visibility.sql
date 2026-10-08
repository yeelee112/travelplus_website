-- Run once on hosting when deploying the shared Domestic / Inbound feature.
ALTER TABLE `tours`
    ADD COLUMN `show_on_inbound` TINYINT(1) NOT NULL DEFAULT 0;
-- Clear the application cache after applying this SQL (php spark cache:clear).

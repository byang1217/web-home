----
-- phpLiteAdmin database dump (http://phpliteadmin.googlecode.com)
-- phpLiteAdmin version: 1.9.5
-- Exported: 1:16am on August 31, 2016 (UTC)
-- database file: ../../FamilyCloud/data/FamilyCloud.db
----
BEGIN TRANSACTION;

----
-- Drop table for albums
----
DROP TABLE "albums";

----
-- Table structure for albums
----
CREATE TABLE 'albums' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'name' TEXT,'cover_photo_id' INTEGER, 'is_from_sync' TEXT DEFAULT 'n','is_public' TEXT DEFAULT 'n');

----
-- Data dump for albums, a total of 0 rows
----

----
-- Drop table for bookmark
----
DROP TABLE "bookmark";

----
-- Table structure for bookmark
----
CREATE TABLE 'bookmark' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'name' TEXT, 'cover_photo_id' INTEGER, 'is_public' TEXT DEFAULT 'n');

----
-- Data dump for bookmark, a total of 0 rows
----

----
-- Drop table for devices
----
DROP TABLE "devices";

----
-- Table structure for devices
----
CREATE TABLE 'devices' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'name' TEXT, 'user_id' INTEGER, 'lastLocalIdentifier' TEXT, 'lastUploadOffset' NUMERIC, 'lastUploadLength' NUMERIC, 'lastUploadTotalLength' NUMERIC, 'uuid' TEXT);

----
-- Data dump for devices, a total of 0 rows
----

----
-- Drop table for note
----
DROP TABLE "note";

----
-- Table structure for note
----
CREATE TABLE 'note' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'user_id' INTEGER, 'photo_id' INTEGER, 'unix_time' NUMERIC, 'text' TEXT);

----
-- Data dump for note, a total of 0 rows
----

----
-- Drop table for photo
----
DROP TABLE "photo";

----
-- Table structure for photo
----
CREATE TABLE 'photo' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'seq_id' NUMERIC, 'uuid' TEXT, 'localIdentifier' TEXT, 'unix_time' NUMERIC, 'path' TEXT, 'type' TEXT, 'note' TEXT, 'is_lock' TEXT DEFAULT 'n', 'is_star' TEXT DEFAULT 'n', 'is_upload' TEXT DEFAULT 'n', 'is_convert' TEXT DEFAULT 'n', 'is_delete' TEXT DEFAULT 'n', 'gps_geoLat' TEXT, 'gps_geoLong' TEXT, 'country' TEXT, 'province' TEXT, 'city' TEXT, 'district' TEXT, 'street' TEXT, 'address' TEXT, 'user_id' INTEGER, 'dev_id' INTEGER);

----
-- Data dump for photo, a total of 0 rows
----

----
-- Drop table for photo_and_album
----
DROP TABLE "photo_and_album";

----
-- Table structure for photo_and_album
----
CREATE TABLE 'photo_and_album' ('photo_id' INTEGER, 'album_id' INTEGER);

----
-- Data dump for photo_and_album, a total of 0 rows
----

----
-- Drop table for photo_and_bookmark
----
DROP TABLE "photo_and_bookmark";

----
-- Table structure for photo_and_bookmark
----
CREATE TABLE 'photo_and_bookmark' ('photo_id' INTEGER, 'bookmark_id' INTEGER);

----
-- Data dump for photo_and_bookmark, a total of 0 rows
----

----
-- Drop table for setup
----
DROP TABLE "setup";

----
-- Table structure for setup
----
CREATE TABLE 'setup' ('config' TEXT, 'value' TEXT);

----
-- Data dump for setup, a total of 0 rows
----

----
-- Drop table for user
----
DROP TABLE "user";

----
-- Table structure for user
----
CREATE TABLE 'user' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'name' TEXT, 'full_name' TEXT, 'privilege' INTEGER DEFAULT 0 , 'is_admin' TEXT DEFAULT 'n', 'token' TEXT);

----
-- Data dump for user, a total of 0 rows
----
COMMIT;

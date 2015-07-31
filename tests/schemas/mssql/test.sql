--
-- Table structure for table "parents"
--

CREATE TABLE "parents" (
  "parent_id" int  NOT NULL IDENTITY(1,1),
  "parent_name" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("parent_id")
);

--
-- Table structure for table "children"
--

CREATE TABLE "children" (
  "child_id" int  NOT NULL IDENTITY(1,1),
  "parent_id" int  DEFAULT NULL,
  "child_name" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("child_id"),
  INDEX "parent_id" ("parent_id"),
  CONSTRAINT "children_ibfk_1" FOREIGN KEY ("parent_id") REFERENCES "parents" ("parent_id") ON DELETE CASCADE ON UPDATE CASCADE
);

--
-- Table structure for table "friends"
--

CREATE TABLE "friends" (
  "friend_id" int  NOT NULL IDENTITY(1,1),
  "friend_name" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("friend_id")
);

--
-- Table structure for table "parent_friends"
--

CREATE TABLE "parent_friends" (
  "parent_friend_id" int  NOT NULL IDENTITY(1,1),
  "parent_id" int  NOT NULL,
  "friend_id" int  NOT NULL,
  "parent_friend_relation" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("parent_friend_id"),
  INDEX "parent_id" ("parent_id"),
  INDEX "friend_id" ("friend_id"),
  CONSTRAINT "parent_friends_ibfk_1" FOREIGN KEY ("parent_id") REFERENCES "parents" ("parent_id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "parent_friends_ibfk_2" FOREIGN KEY ("friend_id") REFERENCES "friends" ("friend_id") ON DELETE CASCADE ON UPDATE CASCADE
);

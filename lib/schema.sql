CREATE TABLE users (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	username TEXT UNIQUE NOT NULL,
	password TEXT NOT NULL
);

CREATE TABLE post (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	user INTEGER NOT NULL,
	filename TEXT,
	extension TEXT,
	width TEXT,
	message TEXT,
	date TEXT
);

CREATE INDEX "name" ON "post" ("filename");
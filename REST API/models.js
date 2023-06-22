const mongoose = require('mongoose');
const Schema = mongoose.Schema;

// Define your schemas
const UserSchema = new Schema({
    Username: { type: String, required: true },
    Password: { type: String, required: true },
}, { versionKey: false });

const MusicSchema = new Schema({
    rank: Number,
    imgurl: String,
    title: String,
    singer: String
}, { versionKey: false });

// Create separate mongoose connections
const UserDB = mongoose.createConnection(process.env.MONGO_CONNECTION_STRING_USERS, { useNewUrlParser: true, useUnifiedTopology: true });
const MergeDB = mongoose.createConnection(process.env.MONGO_CONNECTION_STRING_MUSIC, { useNewUrlParser: true, useUnifiedTopology: true });

// Bind the User and Music models to the mongoose connections
const User = UserDB.model('User', UserSchema, 'users');
const Merge = MergeDB.model('Music', MusicSchema, 'Merge');
module.exports = { User, Merge, MusicSchema};

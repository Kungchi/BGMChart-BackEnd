const mongoose = require('mongoose');
const Schema = mongoose.Schema;

const UserSchema = new Schema({
    Username: { type: String, required: true },
    Password: { type: String, required: true },
});

module.exports = mongoose.model('Users', UserSchema, 'users');


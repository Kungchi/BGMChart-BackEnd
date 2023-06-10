require('dotenv').config({ path: __dirname + '/../.env' });

const express = require('express');
const mongoose = require('mongoose');
const bodyParser = require('body-parser');
const cors = require('cors');
const User = require('./User');

const app = express();

app.use(cors());
app.use(bodyParser.json());

mongoose.connect(process.env.MONGO_CONNECTION_STRING_NODEJS, { useNewUrlParser: true, useUnifiedTopology: true });

app.post('/register', async (req, res) => {
    const { Username, Password } = req.body;

    const user = new User({ Username, Password });
    await user.save();

    res.status(201).send({ message: 'User created' });
});

app.post('/login', async (req, res) => {
    const { Username, Password } = req.body;
    console.log(`Login attempt: ${Username}`); // 로그 추가

    const user = await User.findOne({ Username, Password });
    if (!user) {
        console.log(`Login failed for: ${Username}`); // 로그 추가
        return res.status(400).send({ message: 'Invalid Username or Password' });
    }

    console.log(`Login successful for: ${Username}`); // 로그 추가
    res.status(200).send({ message: 'Logged in' });
});


app.listen(3000, () => {
    console.log('Server is running...');
});

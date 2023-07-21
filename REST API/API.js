require('dotenv').config({ path: __dirname + '/../.env' });

const express = require('express');
const mongoose = require('mongoose');
const bodyParser = require('body-parser');
const cors = require('cors');
const jwt = require('jsonwebtoken');
const axios = require('axios');
const cheerio = require('cheerio');

const {User, Merge, MusicSchema} = require('./models');

const app = express();

const PlayListDB = mongoose.createConnection(process.env.MONGO_CONNECTION_STRING_PLAYLIST, { useNewUrlParser: true, useUnifiedTopology: true });

app.use(cors());
app.use(bodyParser.json());

app.post('/register', async (req, res) => {
    const { Username, Password } = req.body;
    const user = new User({ Username, Password });
    await user.save();
    const token = jwt.sign({ Username: user.Username }, process.env.JWT_SECRET, { expiresIn: '1h' }); // 1 hour
    res.status(201).send({ message: 'User created', token });
});

app.post('/login', async (req, res) => {
    const { Username, Password } = req.body;
    const user = await User.findOne({ Username, Password });
    if (!user) {
        console.log(`Login failed for: ${Username}`); // 로그 추가
        return res.status(400).send({ message: 'Invalid Username or Password' });
    }

    const token = jwt.sign({ Username: user.Username }, process.env.JWT_SECRET, { expiresIn: '1h' }); // 1 hour
    res.status(200).send({ message: 'Logged in', token });
});

app.get('/Merge', async (req, res) => {
    const merge = await Merge.find();
    res.status(200).send(merge);
});

app.get('/PlayList', async (req, res) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (token == null) return res.sendStatus(401);

    jwt.verify(token, process.env.JWT_SECRET, (err, user) => {
        if (err) return res.sendStatus(403);
        req.Username = user.Username;
    });

    const PlayList = PlayListDB.model('Music', MusicSchema, req.Username);
    const playlist = await PlayList.find();
    res.status(200).send(playlist);
});

app.post('/crawl', async (req, res) => {
    const { keyword, page } = req.body;
    const url = `https://music.bugs.co.kr/search/track?q=${keyword}&page=${page}`;
    console.log('url', url);

    const response = await axios.get(url);
    const $ = cheerio.load(response.data);
    
    const musicList = [];
    for (let i = 1; i <= 50; i++) {
        const imgurl = $(`#DEFAULT0 > table > tbody > tr:nth-child(${i}) > td:nth-child(4) > a > img`).attr('src');
        const title = $(`#DEFAULT0 > table > tbody > tr:nth-child(${i}) > th > p > a`).attr('title');
        let singer = $(`#DEFAULT0 > table > tbody > tr:nth-child(${i}) > td:nth-child(7) > p > a`).text();

        // 아이템이 없는 경우 루프를 종료합니다.
        if (!title && !singer && !imgurl) break;

        musicList.push({ title, singer, imgurl });
    }
    res.status(200).send(musicList);
});



app.listen(3000, () => {
    console.log('Server is running...');
});

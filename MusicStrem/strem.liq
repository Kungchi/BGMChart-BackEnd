#!/usr/bin/liquidsoap
Balladeplaylist = playlist.once("/home/ubuntu/BGM_Back/MusicStrem/Ballade")
Danceplaylist = playlist.once("/home/ubuntu/BGM_Back/MusicStrem/Dance")
Folkplaylist = playlist.once("/home/ubuntu/BGM_Back/MusicStrem/Folk")
HipHopplaylist = playlist.once("/home/ubuntu/BGM_Back/MusicStrem/HipHop")
Indieplaylist = playlist.once("/home/ubuntu/BGM_Back/MusicStrem/Indie")
RBplaylist = playlist.once("/home/ubuntu/BGM_Back/MusicStrem/RB")
Rockplaylist = playlist.once("/home/ubuntu/BGM_Back/MusicStrem/Rock")
Trotplaylist = playlist.once("/home/ubuntu/BGM_Back/MusicStrem/Trot")

# Icecast에 스트리밍
output.icecast(%mp3, host = "localhost", port = 8000, password = "hackme", mount = "/Balade.mp3", mksafe(Balladeplaylist))
output.icecast(%mp3, host = "localhost", port = 8000, password = "hackme", mount = "/Dance.mp3", mksafe(Danceplaylist))
output.icecast(%mp3, host = "localhost", port = 8000, password = "hackme", mount = "/Folk.mp3", mksafe(Folkplaylist))
output.icecast(%mp3, host = "localhost", port = 8000, password = "hackme", mount = "/HipHop.mp3", mksafe(HipHopplaylist))
output.icecast(%mp3, host = "localhost", port = 8000, password = "hackme", mount = "/Indie.mp3", mksafe(Indieplaylist))
output.icecast(%mp3, host = "localhost", port = 8000, password = "hackme", mount = "/RB.mp3", mksafe(RBplaylist))
output.icecast(%mp3, host = "localhost", port = 8000, password = "hackme", mount = "/Rock.mp3", mksafe(Rockplaylist))
output.icecast(%mp3, host = "localhost", port = 8000, password = "hackme", mount = "/Trot.mp3", mksafe(Trotplaylist))
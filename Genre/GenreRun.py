import Genre
import video

genreList = [["발라드", "Ballade", 0],
             ["댄스", "Dance", 0], 
             ["포크", "Folk", 0],
             ["힙합", "HipHop", 0],
             ["인디", "Indie", 1],
             ["알앤비", "RB", 1],
             ["락", "Rock", 1],
             ["트로트", "Trot", 1]]

for i in range(len(genreList)):
    Genre.GenreData(genreList[i][0], genreList[i][1], genreList[i][2])
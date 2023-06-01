from flask import Flask, render_template, Response, send_file
from flask_socketio import SocketIO, join_room, leave_room

app = Flask(__name__)
app.config['SECRET_KEY'] = 'secret!'
socketio = SocketIO(app)

@socketio.on('message')
def handle_message(data):
    socketio.send(data, room=data['room'])

@socketio.on('join')
def on_join(data):
    room = data['room']
    join_room(room)
    socketio.send({'msg': data['userName'] + ' has entered the room. Dance'}, room=room)

@socketio.on('leave')
def on_leave(data):
    room = data['room']
    socketio.send({'msg': data['userName'] + ' has left the room.'}, room=room)
    leave_room(room)
    
def generate():
    with open("/home/ubuntu/BGM_Back/Chatroom/music/test2.mp3", "rb") as fmp3:
        data = fmp3.read(1024)
        while data:
            yield data
            data = fmp3.read(1024)

@app.route('/stream')
def stream():
    return Response(generate(), mimetype='audio/mpeg')

if __name__ == '__main__':
    socketio.run(app, host='0.0.0.0', port=5001, allow_unsafe_werkzeug=True)
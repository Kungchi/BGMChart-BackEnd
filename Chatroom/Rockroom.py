from flask import Flask, render_template
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
    socketio.send({'msg': data['userName'] + ' has entered the room. Rock'}, room=room)

@socketio.on('leave')
def on_leave(data):
    room = data['room']
    socketio.send({'msg': data['userName'] + ' has left the room.'}, room=room)
    leave_room(room)

if __name__ == '__main__':
    socketio.run(app, host='0.0.0.0', port=5006, allow_unsafe_werkzeug=True)
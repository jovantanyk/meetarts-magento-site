
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 
 var https = require('https');
 var fs = require('fs');
 var siofu = require('socketio-file-upload');
 
 var options_https = {
     key: fs.readFileSync('pub/media/server_files/default/server.key', 'utf8'),
     cert: fs.readFileSync('pub/media/server_files/default/server.crt', 'utf8'),
     ca: fs.readFileSync('pub/media/server_files/default/server.cabundle'),
     requestCert: true,
     rejectUnauthorized: false
 };
 
 var app = https.createServer(options_https, function (req, res) {
     res.writeHead(200, { 'Content-Type': 'text/plain' });
     res.end('okay');
 });
 
 var io = require('socket.io')(app);
 
 var roomUsers = {};
 
 app.listen(80, function () {
     console.log('listening');
 });
 
 io.on('connection', function (socket) {
 
     var uploader = new siofu();
 
     // Do something when a file is saved:
     uploader.on("saved", function (event) {
         console.log(event.file);
         event.file.clientDetail.fileName = event.file.name;
     });
 
     // Error handler:
     uploader.on("error", function (event) {
         console.log("Error from uploader", event);
     });
 
     uploader.uploadValidator = function (event, callback) {
         fs.mkdir('pub/media/marketplace/chatsystem', function (err, folder) {
             if (err) {
                 if (err.code == 'EEXIST') {
                     uploader.dir = err.path;
                     callback(true);
                 } else {
                     callback(false); // abort
                 }
             }
             else {
                 uploader.dir = folder;
                 callback(true); // ready
             }
         });
     };
 
     uploader.listen(socket);
 
 
     socket.on('newSellerConneted', function (details) {
         var index = details.sellerUniqueId;
         roomUsers[index] = socket.id;
     });
     socket.on('newCustomerConneted', function (details) {
         var index = details.customerUniqueId;
         roomUsers[index] = socket.id;
         Object.keys(roomUsers).forEach(function (key, value) {
             if (key === details.receiverUniqueId) {
                 receiverSocketId = roomUsers[key];
                 socket.broadcast.to(receiverSocketId).emit('refresh seller chat list', details);
             }
         });
     });
 
     socket.on('customer send new message', function (data) {
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 if (key === data.receiverUniqueId) {
                     receiverSocketId = roomUsers[key];
                     socket.broadcast.to(receiverSocketId).emit('seller new message received', data);
                 }
             });
         }
     });
     socket.on('seller send new message', function (data) {
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 if (key === data.receiverUniqueId) {
                     receiverSocketId = roomUsers[key];
                     socket.broadcast.to(receiverSocketId).emit('customer new message received', data);
                 }
             });
         }
     });
     socket.on('customer block event', function (data) {
         console.log('customer-block-event');
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 if (key === data.customerUniqueId) {
                     receiverSocketId = roomUsers[key];
                     socket.broadcast.to(receiverSocketId).emit('customer blocked by seller', data);
                 }
             });
         }
     });
     socket.on('customer status change', function (data) {
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 if (key === data.sellerUniqueId) {
                     receiverSocketId = roomUsers[key];
                     socket.broadcast.to(receiverSocketId).emit('send customer status change', data);
                 }
             });
         }
     });
 
     socket.on('seller status change', function (data) {
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 Object(data.customers).forEach(function (k) {
                     if (key === k.customerUniqueId) {
                         receiverSocketId = roomUsers[key];
                         socket.broadcast.to(receiverSocketId).emit('send seller status change', data);
                     }
                 });
             });
         }
     });
 });
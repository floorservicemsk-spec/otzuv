const bcrypt = require('bcrypt');

const password = 'nifrit2303!@#';
const saltRounds = 10;

bcrypt.hash(password, saltRounds, function(err, hash) {
    if (err) {
        console.error(err);
        process.exit(1);
    }
    console.log(hash);
});

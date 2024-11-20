import express from 'express';
import userRegister from '../controller/userRegister.js';
import userLogin from '../controller/userLogin.js';
import authToken from '../middleware/authToken.js';
import userLogout from '../controller/userLogout.js';
import userDashboard from '../controller/usersDashboard.js';
const router = express.Router();


router.post('/register', userRegister);
router.post('/login', userLogin)
router.post('/logout', authToken, userLogout);
router.get('/dashboard', authToken, userDashboard);
export default router;
// import {JWT} from 'jsonwebtoken';

// const authToken = async (req,res, next)=> {
//     try {
//         const token = req.headers.authorization?.split(' ')[1];
//         if(!token){
//             return res.json({message: "please Login"})
//         }
//         JWT.verify(token, process.env.TOKEN_SECRET_KEY, (err,decoded)=> {
//             if(err){
//                 console.log("error auth", err)
//             }
//             req.userId = decoded?._id;
//             next();
//         });
//     } catch (error) {
//         console.log(error)
//         res.status(400).json({message: "Internal Server Error"});
//     }
// }


// export default authToken;

import JWT from 'jsonwebtoken';

const authToken = (req, res, next) => {
    try {
        // Get the token from the cookies
        const token = req.cookies?.token || req.header("Authorization")?.replace('Bearer ', "");  // Use req.cookies to access the token from the cookie
        // console.log(token)
        if (!token) {
            return res.status(401).json({ message: "Please log in" });
        }

        // Verify the token
        JWT.verify(token, process.env.TOKEN_SECRET_KEY, (err, decoded) => {
            // console.log(decoded)
            if (err) {
                return res.status(403).json({ message: "Invalid or expired token" });
            }
            // Attach the user information to the request object
            req.userId = decoded._id;
            // Proceed to the next middleware or route handler
            next();
        });
    } catch (error) {
        console.error(error);
        res.status(500).json({ message: "Internal Server Error", error: error.message });
    }
};

export default authToken;

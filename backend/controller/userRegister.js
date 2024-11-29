// import { User } from "../models/user.model.js";
// import bcryptjs from 'bcryptjs';

// const userRegister = async (req, res) => {
//   try {
//     const { name, age, qualification, email, password } = req.body;
//     if (!name) {
//       return res.status(400).json({ message: "Name is required" });
//     }
//     if(!age){
//       return res.status(400).json({ message: "Age is required" });
//     }
//     if(!qualification){
//       return res.status(400).json({ message: "Qualification is required" });
//     }
//     if (!email) {
//       return res.status(400).json({ message: "email is required" });
//     }
//     if (!password) {
//       return res.status(400).json({ message: "Password is required" });
//     }
//     const existingUser = await User.findOne({ email });
//     if (existingUser) {
//       return res.status(400).json({ message: "Email already exists" });
//     }

//     const salt = bcryptjs.genSaltSync(10);
//     const hashPassword = bcryptjs.hashSync(password, salt);
//     if(!hashPassword){
//       res.status(500).json({eorr: "somthing is wrong"})
//     }
//     const payload = {
//       ...req.body,
//       password:hashPassword
//     }
//     const user = new User(payload);
//     const savedUser = await user.save();
//     res.status(201).json({ message: "User created successfully", user: savedUser });
//   } catch (error) {
//     res
//       .status(500)
//       .json({ message: "Internal server error", error: error.message });
//   }
// };

// export default userRegister;


import { User } from "../models/user.model.js";
import bcryptjs from 'bcryptjs';

const userRegister = async (req, res) => {
    try {
        const { name, age, qualification, email, password } = req.body;

        // Validate required fields
        if (!name || !email || !password) {
            return res.status(400).json({
                message: "Name, email, and password are required fields.",
            });
        }

        // Check if the user already exists
        const existingUser = await User.findOne({ email });
        if (existingUser) {
            return res.status(400).json({ message: "Email already exists" });
        }

        // Hash the password
        const salt = bcryptjs.genSaltSync(10);
        const hashPassword = bcryptjs.hashSync(password, salt);
        if (!hashPassword) {
            return res.status(500).json({ error: "Something went wrong with password encryption" });
        }

        // Prepare user data
        const userPayload = {
            name,
            email,
            password: hashPassword,
            age: age || null, // Allow null if not provided
            qualification: qualification || null // Allow null if not provided
        };

        // Save the user to MongoDB
        const newUser = new User(userPayload);
        const savedUser = await newUser.save();

        res.status(201).json({
            message: "User created successfully",
            user: savedUser,
        });
    } catch (error) {
        res.status(500).json({
            message: "Internal server error",
            error: error.message,
        });
    }
};

export default userRegister;
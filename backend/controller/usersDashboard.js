import { User } from "../models/user.model.js"

// const userDashboard = async (req, res)=> {
//     try {
//         const getUsers = await User.find();
//         return res.status(200).json({getUsers});
//     } catch (error) {
//         res.status(500)
//       .json({ message: "Internal server error", error: error.message });
//     }
// }




const userDashboard = async (req, res) => {
    try {
        const users = await User.find({}, { _id: 1, name: 1, email: 1 }); // Select only required fields
        res.status(200).json({ success: true, getUsers: users });
    } catch (error) {
        res.status(500).json({ message: "Internal server error", error: error.message });
    }
};
export default userDashboard;
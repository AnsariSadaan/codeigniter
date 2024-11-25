import { User } from "../models/user.model.js";

const userDelete = async (req, res) => {
    try {
        const {_id} = req.body;
        const user = await User.findByIdAndDelete(_id);
        if(!user) return res.status(404).json({message: "User not found"});
        res.status(200).json({message: "User deleted successfully"});
    } catch (error) {
        res.status(500).json({message: "Error deleting user"});
    }
}


export default userDelete;
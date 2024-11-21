import { User } from "../models/user.model.js";

const userEdit = async (req, res) => {
  try {
    const { id } = req.params;
    const { name, email } = req.body;
    const user = await User.findByIdAndUpdate(
      id,
      { name, email },
      { new: true }
    );
    await user.save();
    res.status(200).json({ message: "data editted successfully" });
  } catch (error) {
    res.status(500).json({ message: "error while editing data" });
  }
};


export default userEdit;
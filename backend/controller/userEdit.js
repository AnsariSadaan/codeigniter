import { User } from "../models/user.model.js";  // Assuming the User model is properly imported
import { ObjectId } from 'mongodb';  // Make sure to import ObjectId if it's needed

const userEdit = async (req, res) => {
  try {
    const { id, name, email } = req.body;
    console.log(req.body);
    // Ensure MongoDB ID is valid
    if (!ObjectId.isValid(id)) {
      return res.status(400).json({ message: 'Invalid MongoDB ID' });
    }

    // Find and update the user in MongoDB
    const user = await User.findByIdAndUpdate(id, { name, email }, { new: true });

    if (!user) {
      return res.status(404).json({ message: 'User not found in MongoDB' });
    }

    res.status(200).json({ message: 'Data edited successfully' });
  } catch (error) {
    console.error('Error while editing data in MongoDB:', error);
    res.status(500).json({ message: 'Error while editing data' });
  }
};


export default userEdit;

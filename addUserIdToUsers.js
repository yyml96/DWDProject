import { MongoClient } from 'mongodb';

async function updateUsers() {
    const uri = 'mongodb+srv://zhx1346:dmqVcDWWqUmHvqIg@dynamicqc.jgtbu.mongodb.net/?retryWrites=true&w=majority&appName=dynamicQC';
    const client = new MongoClient(uri, { useNewUrlParser: true, useUnifiedTopology: true });

    try {
        console.log('Connecting to the database...');
        await client.connect();
        console.log('Connected to the database.');

        const database = client.db('quality_control');
        const users = database.collection('users');

        console.log('Fetching users...');
        const cursor = users.find();
        let idCounter = 1;

        while (await cursor.hasNext()) {
            const user = await cursor.next();
            await users.updateOne(
                { _id: user._id },
                { $set: { id: idCounter }, $unset: { userId: "" } }
            );
            idCounter++;
        }

        console.log(`All users have been updated with id and userId removed. Total users updated: ${idCounter - 1}`);
    } catch (error) {
        console.error('An error occurred:', error);
    } finally {
        await client.close();
        console.log('Database connection closed.');
    }
}

updateUsers().catch(console.error);
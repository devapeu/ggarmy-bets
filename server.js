const express = require('express');
const cors = require('cors');
const axios = require('axios');

const app = express();
app.use(cors());

const CHALLONGE_API_KEY = process.env.CHALLONGE_API_KEY;
const BASE_URL = 'https://api.challonge.com/v1';

app.get('/api/tournaments/:tournamentId/matches', async (req, res) => {
  try {
    const { tournamentId } = req.params;
    const response = await axios.get(
      `${BASE_URL}/tournaments/${tournamentId}/matches.json?api_key=${CHALLONGE_API_KEY}`
    );
    res.json(response.data);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

app.listen(3000, () => {
  console.log('Server running on port 3000');
}); 
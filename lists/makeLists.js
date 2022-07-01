const fs = require('fs')
function makeList(data, filename) {
  console.log("Processing " + filename);
    fs.writeFile(`./${filename}.json`, JSON.stringify(data), err => {
      if (err) {console.log(`Problem writing file: ${filename}: ${err}`);}
    });
  }

states = {
  description: "U.S. States",
  data: ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware',  'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Northern Mariana Islands', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Puerto Rico', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'],
  special:'Georgia',
  favorites: [2, 5, 15, 23]
}
makeList(states, "states");


provinces = {
  description: "Canadian Provinces and Territories",
  data: ['Alberta', 'British Columbia', 'Manitoba', 'New Brunswick', 'Newfoundland and Labrador', 'Northwest Territories', 'Nova Scotia', 'Nunavut', 'Ontario', 'Prince Edward Island', 'Quebec', 'Saskatchewan', 'Yukon Territory'],
  special: 'Manitoba',
  favorites: [1, 2]
}
makeList(provinces, "provinces");

plays = {
  description: 'Shakespeare Plays',
  data: ['Two Gentlemen of Verona', 'Taming of the Shrew',
    'Henry VI, part 1',
    'Henry VI, part 3',
    'Titus Andronicus',
    'Henry VI, part 2',
    'Richard III',
    'The Comedy of Errors',
    'Love\'s Labours Lost',
    'A Midsummer Night\'s Dream',
    'Romeo and Juliet',
    'Richard II',
    'King John',
    'The Merchant of Venice',
    'Henry IV, part 1',
    'The Merry Wives of Windsor',
    'Henry IV, part 2',
    'Much Ado About Nothing',
    'Henry V',
    'Julius Caesar',
    'As You Like It',
    'Hamlet',
    'Twelfth Night',
    'Troilus and Cressida',
    'Measure for Measure',
    'Othello',
    'All\'s Well That Ends Well',
    'Timon of Athens',
    'The Tragedy of King Lear',
    'Macbeth',
    'Anthony and Cleopatra',
    'Pericles, Prince of Tyre',
    'Coriolanus',
    'Winter\'s Tale',
    'Cymbeline',
    'The Tempest',
    'Henry VIII'],
    special: 'Taming of the Shrew',
    favorites: [5, 7, 12]
}
makeList(plays, "plays");

starwars = {
  description: 'Star Wars Movies',
  data: ['The Phantom Menace', 'Attack of the Clones', 'Revenge of the Sith', 
  'A New Hope', 'The Empire Strikes Back', 'Return of the Jedi',
  'The Force Awakens', 'The Last Jedi', 'The Rise of Skywalker'],
  special: 'The Phantom Menace',
  favorites: [3, 4, 5]
}
makeList(starwars, "starwars")
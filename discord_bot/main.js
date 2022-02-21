const fs = require('fs');
// require('log-timestamp');

const {Client, MessageEmbed} = require("discord.js");
const config = require("../data/config.json");

const prefix = "!";

const bot = new Client({intents: ["GUILDS", "GUILD_MESSAGES"]});

bot.login(config.BOT_TOKEN);
console.log("Bot started");

bot.on("messageCreate", function(message) {
    if (message.author.bot) return;
    if (!message.content.startsWith(prefix)) return;

    const commandBody = message.content.slice(prefix.length);
    const args = commandBody.split(' ');
    const command = args.shift().toLowerCase();

    if (config.debug == "DEV") {
        server = "851114680994103336";
    } else {
        server = "795555470164754482";
    }

    message.reply('Commands are not working yet');
});

var readJson = (path, cb) => {fs.readFile(require.resolve(path), (err, data) => {if (err){cb(err)}else{cb(null, JSON.parse(data))}})}

fs.watchFile('../data/changes.json', () => {
    console.log('File changed');

    if (config.debug == "DEV") {
        server = "851114680994103336";
    } else {
        server = "795555470164754482";
    }

    readJson('../data/changes.json', (err, updates) => {
        if (updates.length > 0) {
            console.log('Sending '+updates.length+' messages');
            const notify_channel_id = config['servers'][server]['notify'];
            const notify_channel = bot.channels.cache.get(notify_channel_id);
            updates.forEach(update => {
                const embed = new MessageEmbed()
                    .setColor(update.color.toString())
                    .setTitle(update.title.toString())
                    .setDescription(update.description.toString());
                update.fields.forEach(field => {
                    embed.addField(field.name.toString(), field.value.toString(), true);
                });
                const ping = config['servers'][server]['roles'][update.vak];
                notify_channel.send({content: `<@&${ping}>`, embeds: [embed]});
                
            });
            console.log('Resetting changes.json');
            fs.writeFileSync('../data/changes.json', '[]');
            var currentdate = new Date();
            var datetime = "Laatste update: " + currentdate.getDate() + "/"
                        + (currentdate.getMonth()+1)  + "/" 
                        + currentdate.getFullYear() + " @ "  
                        + currentdate.getHours() + ":"  
                        + currentdate.getMinutes() + ":" 
                        + currentdate.getSeconds();
            notify_channel.setTopic(datetime)
            .then(updated => console.log(`Channel's new topic is ${updated.topic}`))
            .catch(console.error);
        } else {
            console.log('Empty');
        }
    });

    
});
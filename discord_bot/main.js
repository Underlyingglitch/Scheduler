const fs = require('fs');
// require('log-timestamp');

const {Client, MessageEmbed} = require("discord.js");
const config = require("./config.json");

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
    

});

fs.watchFile('../events/updates.json', () => {
    console.log('File changed');

    const notify_channel_id = config['channels']['851114680994103336']['notify'];
    const notify_channel = bot.channels.cache.get(notify_channel_id);

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

    var updates = require("../events/updates.json");

    updates.forEach(update => {
        const embed = new MessageEmbed()
            .setColor(update.color)
            .setTitle(update.title)
            .setDescription(update.description);
        update.fields.forEach(field => {
            embed.addField(field.name, field.value, true);
        });
        const ping = config.roles[update.vak];
        notify_channel.send({content: `<@&${ping}>`, embeds: [embed]});
        
    });
});
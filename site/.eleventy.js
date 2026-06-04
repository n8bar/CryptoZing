const isCI = process.env.CI === 'true';

module.exports = function (eleventyConfig) {
  eleventyConfig.addFilter("date", function (value, format) {
    const d = new Date(value);
    const months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
    return format
      .replace("%B", months[d.getUTCMonth()])
      .replace("%d", String(d.getUTCDate()).padStart(2, "0"))
      .replace("%Y", d.getUTCFullYear());
  });

  // Ordered guide lists: newest-first, but the video card is pulled out of its
  // date slot and placed directly after the article it pairs with.
  const pairAfter = "accepting-bitcoin-payments-freelancer-small-business";
  const orderGuides = (items) => {
    const ordered = items.slice().reverse();
    const video = ordered.find((i) => i.data.card_image);
    if (!video) return ordered;
    const rest = ordered.filter((i) => i !== video);
    const idx = rest.findIndex((i) => i.fileSlug === pairAfter);
    rest.splice(idx === -1 ? rest.length : idx + 1, 0, video);
    return rest;
  };
  eleventyConfig.addCollection("learnList", (api) => orderGuides(api.getFilteredByTag("learn")));
  eleventyConfig.addCollection("stagingList", (api) => orderGuides(api.getFilteredByTag("staging")));

  // Passthroughs are only needed for the GitHub Pages build.
  // In local dev these files already exist in public/ and don't need copying.
  if (isCI) {
    eleventyConfig.addPassthroughCopy("images");
    eleventyConfig.addPassthroughCopy("og-preview.png");
    eleventyConfig.addPassthroughCopy("og-preview.svg");
    eleventyConfig.addPassthroughCopy("CNAME");
    eleventyConfig.addPassthroughCopy("robots.txt");
    eleventyConfig.addPassthroughCopy(".nojekyll");
    eleventyConfig.addPassthroughCopy("sitemap.xml");
    eleventyConfig.addPassthroughCopy("465536a63a3c14ac146077111999e458.txt");
  }

  return {
    dir: {
      // Local: output directly to public/ so /learn/ matches production URLs.
      // CI: output to _site/ so the Pages artifact doesn't include Laravel files.
      input: ".",
      output: isCI ? "_site" : "../public",
      layouts: "_layouts",
      includes: "_includes",
      data: "_data",
    },
    templateFormats: ["njk", "md", "html"],
    htmlTemplateEngine: "njk",
    markdownTemplateEngine: "njk",
  };
};

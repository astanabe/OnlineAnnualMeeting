t=`TZ=JST-9 date +%Y-%m-%d`
uplatex --kanji=utf8 onlineannualmeeting
upbibtex --kanji=utf8 onlineannualmeeting
perl convertbbl.pl < onlineannualmeeting.bbl > onlineannualmeeting.temp.bbl
rm -f onlineannualmeeting.bbl
mv onlineannualmeeting.temp.bbl onlineannualmeeting.bbl
uplatex --kanji=utf8 onlineannualmeeting
uplatex --kanji=utf8 onlineannualmeeting
dvipdfmx onlineannualmeeting
rm onlineannualmeeting.temp.pdf
perl -i.bak -npe "s/<dc:date>.+<\/dc:date>/<dc:date>$t<\/dc:date>/" onlineannualmeeting.xmp
cpdf -set-metadata onlineannualmeeting.xmp onlineannualmeeting.pdf -o onlineannualmeeting.temp.pdf
rm onlineannualmeeting.pdf
mv onlineannualmeeting.temp.pdf onlineannualmeeting.pdf
perl convert4latexml.pl < onlineannualmeeting.tex > onlineannualmeeting.temp.tex
latexml --xml --nocomments --inputencoding=utf8 --destination=onlineannualmeeting.xml onlineannualmeeting.temp.tex
rm onlineannualmeeting.temp.tex
perl convertxml2xml.pl < onlineannualmeeting.xml > onlineannualmeeting.temp.xml
rm onlineannualmeeting.xml
mv onlineannualmeeting.temp.xml onlineannualmeeting.xml
latexmlpost --format=html5 --crossref --index --mathimages --nomathsvg --nopresentationmathml --nocontentmathml --noopenmath --nomathtex --graphicimages --verbose --destination=onlineannualmeeting.html onlineannualmeeting.xml
perl converthtml2html.ja.pl < onlineannualmeeting.html > onlineannualmeeting.temp.html
rm onlineannualmeeting.html
mv onlineannualmeeting.temp.html onlineannualmeeting.html
perl -i.bak -npe "s/<dc:date>.+<\/dc:date>/<dc:date>$t<\/dc:date>/" onlineannualmeeting.opf
ebook-convert onlineannualmeeting.html onlineannualmeeting.epub --max-toc-links=0 --toc-threshold=1 --level1-toc=//h:h2 --level2-toc=//h:h3 --level3-toc=//h:h4 --read-metadata-from-opf=onlineannualmeeting.opf --preserve-cover-aspect-ratio
kindlegen onlineannualmeeting.epub

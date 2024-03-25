# Lorenzo Calvisi, mat. 634171 
#
# Progetto finale di Linguistica Computazionale, a.a. 2022/2023
#
# PROGRAMMA N. 1

import sys
from nltk.tokenize import word_tokenize, sent_tokenize
from nltk.probability import FreqDist
from nltk.stem import WordNetLemmatizer
from nltk.tag import pos_tag
from statistics import mean

class EmptyFile(Exception): 
    """
    Eccezione sollevata qualora uno dei due file sia vuoto
    """

def rem_punct(tkn): # Rimozione dei segni di punteggiatura
    """
    Funzione che rimuove i segni d'interpunzione.

    Prende in input una lista di token, la copia in "tknum_temp", per non modificare l'originale, e rimuove ove trovati i segni di punteggiatura, elencati nella lista "segniP".
    
    Restituisce la stringa "tknum_temp" ripulita.
    """

    segniP = [',', ';','.', ':', '?', '!'] # Segni di punteggiatura da eliminare all'occorrenza
    tknum_temp = tkn.copy()
    for x in tknum_temp:
        if x in segniP:
            tknum_temp.remove(x)
    return tknum_temp

def avg_token_len(tkn): # Funzione che calcola la lunghezza media dei token
    """
    Funzione che calcola la lunghezza media dei token

    Adoperando una list comprehension, di ciascun token presente nella lista passata come argomento viene calcolata la lunghezza in caratteri.

    Tale misura viene aggiunta alla lista avg_len, di cui viene restituita la media tra tutti i valori presenti.
    """
    avg_len = [len(token) for token in tkn] # Alla lista avg_len aggiungo la lunghezza di ogni token della lista "tkn" passata come argomento
    return mean(avg_len) # Calcolo la media tra tutti i valori di l_m

def vocab_size(tkn): # Calcolo delle dimensioni del vocabolario
    """
    Funzione che calcola le dimensioni del vocabolario.
    La lista dei token viene convertita in un set, che esclude la presenza di elementi identici, di cui poi viene calcolato e restituita il numero di elementi.
    """

    return len(set(tkn)) # Restituisco la dimensione del dizionario

def lemm_num(pos_tkn): # Calcolo del numero di lemmi   
    """
    Funzione che calcola il numero dei lemmi.
    
    La funzione prende in input una lista di tuple, in cui il primo elemento sia il token e il secondo il tag.
    
    La lemmatizzazione viene effettuata grazie al lemmatizer di WordNet, il quale, per funzionare correttamente,
    esige che venga specificato il corretto valore di "pos".
    
    Tale argomento può assumere solamente alcuni valori:
        * a -> aggettivo
        * r -> avverbio
        * n -> nome
        * v -> verbo
    
    Le altre parti del discorso (congiunzioni, pronomi, preposizioni ecc...) vengono aggiunte all'insieme dei lemmi senza passare per la lemmatizzazione,
    limitandosi a portarle in caratteri minuscoli, qualora ve ne fosse bisogno.
    
    La funzione restituisce il numero di elementi presenti nel set lemmatized_corpus al termine dell'iterazione.
    """

    lemmatizer = WordNetLemmatizer()
    lemmatized_corpus = set() # Inizializzo un set per il corpus lemmatizzato. Uso un set al posto di una lista per escludere i duplicati
    for token, tag in pos_tkn: # Per ogni token e tag delle tuple di pos_tagged_token
        wordnet_pos_t = tag[0].lower() # Prendo la prima lettera del tag e la rendo minuscola
        if wordnet_pos_t in ['a', 'r', 'n', 'v']: # Se il risultato è una di queste, significa che la posso usare come tag di WordNet
            lemma = lemmatizer.lemmatize(token, pos=wordnet_pos_t) # Taggo con il tag opportuno
        else:
            lemma = token.lower()
        
        lemmatized_corpus.add(lemma)

    return len(lemmatized_corpus)

def analisi_corpus(corpus, n): # Analisi dei corpus
    """
    Analisi del corpus.

    Funzione centrale del programma, prend in input un corpus (ossia il contenuto di un file di testo) e il suo numero, effettua gli esami richiesti e ne stampa i risultati.
    """
    corpus = corpus.lower()
    token_words = word_tokenize(corpus) # Tokenizzo il corpus 
    token_senteces = sent_tokenize(corpus) # Effettuo il sentence splitting
    no_punct_tokens = rem_punct(token_words) # Elimino la puntegggiatura

    num_token_words = len(token_words) # Quantità di token con punteggiatura
    num_no_punct_tokens = len(no_punct_tokens) # Quantità di token senza punteggiatura
    num_token_senteces = len(token_senteces) # Quantità di frasi
    num_tknum_m = num_no_punct_tokens / num_token_senteces # Quantità media di token per frase
    num_avg_chars_token = avg_token_len(no_punct_tokens) # Quantità media di caratteri per token
    num_lemmi = lemm_num(pos_tag(no_punct_tokens)) # Calcolo il numero di lemmi

    # Stampa dei risultati
    output.write("\n########## CORPUS N. {} ##########\n".format(n))
    output.write("########## {} ##########\n\n".format(sys.argv[n]))
    output.write("* Numero di token: {}\n".format(num_token_words))
    output.write("* Numero di frasi: {}\n".format(num_token_senteces))
    output.write("* Numero medio di token per frase: {}\n".format(num_tknum_m))
    output.write("* Numero medio di caratteri per token: {}\n".format(num_avg_chars_token))
    output.write("* Numero di lemmi: {}\n".format(num_lemmi))
    output.write("* Dimensioni del vocabolario: \n")

    for i in range(200, num_token_words-1, 200):
        vocab = vocab_size(token_words[0:i])
        ttr = vocab / i
        output.write("     Nei primi {} token:\n".format(i))
        output.write("         * Dimensioni Vocabolario: {}\n".format(vocab))
        output.write("         * TTR: {}\n\n".format(ttr))

    for num_words in [500, 1000, 3000, num_token_words-1]:
        fq = FreqDist(token_words[0:num_words])
        hapax = fq.hapaxes()
        if(num_words == num_token_words-1):
            output.write("Hapax in tutto il corpus: {}\n".format(len(hapax)))
        else:
            output.write("Hapax nelle prime {} parole: {}\n".format(num_words, len(hapax)))

    output.write("\n\n")

try:
    file1 = open(sys.argv[1], "r")
    file2 = open(sys.argv[2], "r")

except IndexError: # Qualora uno dei due file non fosse specificato
    print("ERRORE: Non hai specificato tutti gli argomenti\n") # Stampo un messaggio di errore
    print(sys.exc_info()) # Stampo informazioni utili a risolvere il problema

except FileNotFoundError: # Qualora uno dei due file risultasse inesistente
    print("ERRORE: Uno dei due file specificati non esiste\n")
    print(sys.exc_info()[1])

except PermissionError: # Qualora vi siano dei problemi di lettura legati ai permessi 
    print("ERRORE: Impossibile leggere i file. Assicurati che i permessi siano impostati correttamente\n")
    print(sys.exc_info())

else:
    try:
        output = open("programma1_output.txt", "w")
    except PermissionError:
        print("ERRORE: impossibile scrivere sul file output_1.txt o crearlo. Assicurati che i permessi siano impostati correttamente.")
        print(sys.exc_info())
    else:
        # Caricamento dei due corpus
        corpus1 = file1.read()
        corpus2 = file2.read()

        try:
            err = "Il file {} è vuoto" # Messaggio di errore
        
            # Verifico che i file non siano vuoti. Qualora lo siano, inserisco il nome del file vuoto nel messaggio di errore
            if(len(corpus1) == 0):
                raise EmptyFile(err.format(sys.argv[1]))
            elif(len(corpus2) == 0):
                raise EmptyFile(err.format(sys.argv[2]))
           
        except EmptyFile: # Qualora uno di loro lo sia, sollevo un'eccezione opportuna
            print("ERRORE: ", sys.exc_info()[1], "\n")
            print(sys.exc_info())

        else: # Qualora non siano state sollevate eccezioni, eseguo l'analisi dei corpus
        
            # Analisi e stampa dei risultati
            analisi_corpus(corpus1, 1)
        
            analisi_corpus(corpus2, 2)
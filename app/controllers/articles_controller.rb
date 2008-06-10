class ArticlesController < ApplicationController
  before_filter :login_required, :except => [:index, :home, :list, :show]

  def index
    list
    render :action => 'list'
  end

  # GETs should be safe (see http://www.w3.org/2001/tag/doc/whenToUseGet.html)
  verify :method => :post, :only => [ :destroy, :create, :update ],
         :redirect_to => { :action => :list }

  def home
    @articles = Article.find(:all, :conditions => published_sql(self.controller_name), :include => [:author], :order => "articles.created_at, articles.id DESC", :limit => "3")
    @upcoming_events = Event.find(:all, :conditions => "#{published_sql("events", "published")} AND events.date >= CURRENT_DATE() ")
    render :template => 'layouts/home'
  end

  def list
    @article_pages, @articles = paginate :articles, :conditions => published_sql(self.controller_name), :include => [:author], :order => "articles.created_at, articles.id DESC", :per_page => 10
    @page_title = "Articles"
  end

  def show
    @article   = Article.find(params[:id], :conditions => published_sql(self.controller_name))
    @recent_articles = Article.find(:all, :conditions => published_sql(self.controller_name), :order => "articles.created_at, articles.id DESC", :limit => "10")
    @page_title = @article.title if @article
  end

  def new
    @authors = Person.find(:all, :conditions => 'deleted = 0', :order => "fullname ASC")
    @article = Article.new
  end
  
  def create
    @article = Article.new(params[:article])
    @article.author_id = 1
    @article.urlpath = @article.title.downcase.gsub(" ", "-")
    if @article.save
      flash[:notice] = 'Article was successfully created.'
      redirect_to :action => 'list'
    else
      render :action => 'new'
    end
  end

  def edit
    @authors = Person.find(:all, :conditions => 'deleted = 0', :order => "fullname ASC")
    @article = Article.find(params[:id])
  end

  def update
    @article = Article.find(params[:id])
    if @article.update_attributes(params[:article])
      @article.urlpath = @article.title.downcase.gsub(" ", "-")
      @article.save
      flash[:notice] = 'Article was successfully updated.'
      redirect_to :action => 'show', :id => @article
    else
      render :action => 'edit'
    end
  end

  def destroy
    Article.find(params[:id]).destroy
    redirect_to :action => 'list'
  end
end

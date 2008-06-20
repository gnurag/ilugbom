#  Copyright (c) 2008, Anurag Patel <anurag@xinh.org>
#  All rights reserved.
#
#  Redistribution and use in source and binary forms, with or without
#  modification, are permitted provided that the following conditions are met:
#      * Redistributions of source code must retain the above copyright
#        notice, this list of conditions and the following disclaimer.
#      * Redistributions in binary form must reproduce the above copyright
#        notice, this list of conditions and the following disclaimer in the
#        documentation and/or other materials provided with the distribution.
#      * Neither the name of the Xinh Associates nor the
#        names of its contributors may be used to endorse or promote products
#        derived from this software without specific prior written permission.
#
#  THIS SOFTWARE IS PROVIDED BY ANURAG PATEL ``AS IS'' AND ANY
#  EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
#  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
#  DISCLAIMED. IN NO EVENT SHALL ANURAG PATEL BE LIABLE FOR ANY
#  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
#  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
#  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
#  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
#  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
#  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

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
    @articles = Article.find(:all, :conditions => published_sql(self.controller_name), :include => [:author], :order => "articles.created_at DESC", :limit => "3")
    @upcoming_events = Event.find(:all, :conditions => "#{published_sql("events", "published")} AND events.date >= CURRENT_DATE() ")
    render :template => 'layouts/home'
  end

  def list
    @article_pages, @articles = paginate :articles, :conditions => published_sql(self.controller_name), :include => [:author], :order => "articles.created_at DESC", :per_page => 10
    @page_title = "Articles"
  end

  def show
    @article   = Article.find(params[:id], :conditions => published_sql(self.controller_name))
    @recent_articles = Article.find(:all, :conditions => published_sql(self.controller_name), :order => "articles.created_at DESC", :limit => "10")
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
      @authors = Person.find(:all, :conditions => 'deleted = 0', :order => "fullname ASC")
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
      @authors = Person.find(:all, :conditions => 'deleted = 0', :order => "fullname ASC")
      render :action => 'edit'
    end
  end

  def destroy
    Article.find(params[:id]).destroy
    redirect_to :action => 'list'
  end
end
